<?php

/**
 * Request Client. Processes a [Request] and handles [HTTP_Caching] if
 * available. Will usually return a [Response] object as a result of the
 * request unless an unexpected error occurs.
 *
 * @package    Kohana
 * @category   Base
 * @author     Kohana Team
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    https://kohana.top/license
 * @since      3.1.0
 */
abstract class Kohana_Request_Client
{
    /**
     * @var    Cache  Caching library for request caching
     */
    protected $_cache;

    /**
     * @var  bool  Should redirects be followed?
     */
    protected $_follow = false;

    /**
     * @var  array  Headers to preserve when following a redirect
     */
    protected $_follow_headers = ['authorization'];

    /**
     * @var  bool  Follow 302 redirect with original request method?
     */
    protected $_strict_redirect = true;

    /**
     * @var array  Callbacks to use when response contains given headers
     */
    protected $_header_callbacks = [
        'Location' => 'Request_Client::on_header_location'
    ];

    /**
     * @var int  Maximum number of requests that header callbacks can trigger before the request is aborted
     */
    protected $_max_callback_depth = 5;

    /**
     * @var int  Tracks the callback depth of the currently executing request
     */
    protected $_callback_depth = 1;

    /**
     * @var array  Arbitrary parameters that are shared with header callbacks through their Request_Client object
     */
    protected $_callback_params = [];

    /**
     * Creates a new `Request_Client` object,
     * allows for dependency injection.
     *
     * @param   array    $params Params
     */
    public function __construct(array $params = [])
    {
        foreach ($params as $key => $value) {
            if (method_exists($this, $key)) {
                $this->$key($value);
            }
        }
    }

    /**
     * Processes the request, executing the controller action that handles this
     * request, determined by the [Route].
     *
     * 1. Before the controller action is called, the [Controller::before] method
     * will be called.
     * 2. Next the controller action will be called.
     * 3. After the controller action is called, the [Controller::after] method
     * will be called.
     *
     * By default, the output from the controller is captured and returned, and
     * no headers are sent.
     *
     *     $request->execute();
     *
     * @param   Request   $request
     * @return  Response
     * @throws  Kohana_Exception
     * @uses    [Kohana::$profiling]
     * @uses    [Profiler]
     */
    public function execute(Request $request)
    {
        // Prevent too much recursion of header callback requests
        if ($this->callback_depth() > $this->max_callback_depth())
            throw new Request_Client_Recursion_Exception("Could not execute request to :uri - too many recursions after :depth requests", [
            ':uri' => $request->uri(),
            ':depth' => $this->callback_depth() - 1,
            ]);

        // Execute the request and pass the currently used protocol
        $orig_response = $response = Response::factory(['_protocol' => $request->protocol()]);

        if (($cache = $this->cache()) instanceof HTTP_Cache)
            return $cache->execute($this, $request, $response);

        $response = $this->execute_request($request, $response);

        // Execute response callbacks
        foreach ($this->header_callbacks() as $header => $callback) {
            if ($response->headers($header)) {
                $cb_result = call_user_func($callback, $request, $response, $this);

                if ($cb_result instanceof Request) {
                    // If the callback returns a request, automatically assign client params
                    $this->assign_client_properties($cb_result->client());
                    $cb_result->client()->callback_depth($this->callback_depth() + 1);

                    // Execute the request
                    $response = $cb_result->execute();
                } elseif ($cb_result instanceof Response) {
                    // Assign the returned response
                    $response = $cb_result;
                }

                // If the callback has created a new response, do not process any further
                if ($response !== $orig_response)
                    break;
            }
        }

        return $response;
    }

    /**
     * Processes the request passed to it and returns the response from
     * the URI resource identified.
     *
     * This method must be implemented by all clients.
     *
     * @param   Request   $request   request to execute by client
     * @param   Response  $response
     * @return  Response
     * @since   3.2.0
     */
    abstract public function execute_request(Request $request, Response $response);

    /**
     * Getter and setter for the internal caching engine,
     * used to cache responses if available and valid.
     *
     * @param HTTP_Cache|null $cache engine to use for caching
     * @return Cache|Kohana_Request_Client
     */
    public function cache(HTTP_Cache $cache = null)
    {
        if ($cache === null)
            return $this->_cache;

        $this->_cache = $cache;
        return $this;
    }

    /**
     * Getter and setter for the follow redirects
     * setting.
     *
     * @param   bool  $follow  Boolean indicating if redirects should be followed
     * @return bool|Kohana_Request_Client
     */
    public function follow($follow = null)
    {
        if ($follow === null)
            return $this->_follow;

        $this->_follow = $follow;

        return $this;
    }

    /**
     * Getter and setter for the follow redirects
     * headers array.
     *
     * @param   array  $follow_headers  Array of headers to be re-used when following a Location header
     * @return array|Kohana_Request_Client
     */
    public function follow_headers($follow_headers = null)
    {
        if ($follow_headers === null)
            return $this->_follow_headers;

        $this->_follow_headers = array_map('strtolower', $follow_headers);

        return $this;
    }

    /**
     * Getter and setter for the strict redirects setting
     *
     * [!!] HTTP/1.1 specifies that a 302 redirect should be followed using the
     * original request method. However, the vast majority of clients and servers
     * get this wrong, with 302 widely used for 'POST - 302 redirect - GET' patterns.
     * By default, Kohana's client is fully compliant with the HTTP spec. Some
     * non-compliant third party sites may require that strict_redirect is set
     * false to force the client to switch to GET following a 302 response.
     *
     * @param  bool  $strict_redirect  Boolean indicating if 302 redirects should be followed with the original method
     * @return bool|Kohana_Request_Client
     */
    public function strict_redirect($strict_redirect = null)
    {
        if ($strict_redirect === null)
            return $this->_strict_redirect;

        $this->_strict_redirect = $strict_redirect;

        return $this;
    }

    /**
     * Getter and setter for the header callbacks array.
     *
     * Accepts an array with HTTP response headers as keys and a PHP callback
     * function as values. These callbacks will be triggered if a response contains
     * the given header and can either issue a subsequent request or manipulate
     * the response as required.
     *
     * By default, the [Request_Client::on_header_location] callback is assigned
     * to the Location header to support automatic redirect following.
     *
     *     $client->header_callbacks([
     *         'Location' => 'Request_Client::on_header_location',
     *         'WWW-Authenticate' => function($request, $response, $client) {
     *             return $new_response;
     *         }
     *     ];
     *
     * @param array $header_callbacks	Array of callbacks to trigger on presence of given headers
     * @return array|Kohana_Request_Client|string[]
     */
    public function header_callbacks($header_callbacks = null)
    {
        if ($header_callbacks === null)
            return $this->_header_callbacks;

        $this->_header_callbacks = $header_callbacks;

        return $this;
    }

    /**
     * Getter and setter for the maximum callback depth property.
     *
     * This protects the main execution from recursive callback execution (e.g.,
     * following infinite redirects, and conflicts between callbacks causing loops).
     * Requests will only be allowed to nest to the level set by this
     * param before execution is aborted with a Request_Client_Recursion_Exception.
     *
     * @param int $depth  Maximum number of callback requests to execute before aborting
     * @return int|Kohana_Request_Client
     */
    public function max_callback_depth($depth = null)
    {
        if ($depth === null)
            return $this->_max_callback_depth;

        $this->_max_callback_depth = $depth;

        return $this;
    }

    /**
     * Getter/Setter for the callback depth property, which is used to track
     * how many recursions have been executed within the current request execution.
     *
     * @param int $depth  Current recursion depth
     * @return int|Kohana_Request_Client
     */
    public function callback_depth($depth = null)
    {
        if ($depth === null)
            return $this->_callback_depth;

        $this->_callback_depth = $depth;

        return $this;
    }

    /**
     * Getter/Setter for the callback_params array, which allows additional
     * application-specific parameters to be shared with callbacks.
     *
     * As with other Kohana setter/getters, usage is:
     *
     *     // Set full array
     *     $client->callback_params(['foo' => 'bar']);
     *
     *     // Set single key
     *     $client->callback_params('foo','bar');
     *
     *     // Get full array
     *     $params = $client->callback_params();
     *
     *     // Get single key
     *     $foo = $client->callback_params('foo');
     *
     * @param string|array $param
     * @param mixed $value
     * @return Request_Client|mixed
     */
    public function callback_params($param = null, $value = null)
    {
        // Getter for full array
        if ($param === null)
            return $this->_callback_params;

        // Setter for full array
        if (is_array($param)) {
            $this->_callback_params = $param;
            return $this;
        }
        // Getter for single value
        elseif ($value === null) {
            return Arr::get($this->_callback_params, $param);
        }
        // Setter for single value
        else {
            $this->_callback_params[$param] = $value;
            return $this;
        }
    }

    /**
     * Assigns the properties of the current Request_Client to another
     * Request_Client instance - used when setting up a subsequent request.
     *
     * @param Request_Client $client
     */
    public function assign_client_properties(Request_Client $client)
    {
        $client->cache($this->cache());
        $client->follow($this->follow());
        $client->follow_headers($this->follow_headers());
        $client->header_callbacks($this->header_callbacks());
        $client->max_callback_depth($this->max_callback_depth());
        $client->callback_params($this->callback_params());
    }

    /**
     * The default handler for following redirects, triggered by the presence of
     * a Location header in the response.
     *
     * The client's follow property must be set true and the HTTP response status
     * one of 201, 301, 302, 303 or 307 for the redirect to be followed.
     *
     * @param Request $request
     * @param Response $response
     * @param Request_Client $client
     * @return null
     * @throws Kohana_Exception
     * @throws Request_Exception
     */
    public static function on_header_location(Request $request, Response $response, Request_Client $client)
    {
        // Do we need to follow a Location header ?
        if ($client->follow() && in_array($response->status(), [201, 301, 302, 303, 307])) {
            // Figure out which method to use for the follow request
            switch ($response->status()) {
                default:
                case 301:
                case 307:
                    $follow_method = $request->method();
                    break;
                case 201:
                case 303:
                    $follow_method = Request::GET;
                    break;
                case 302:
                    // Cater for sites with broken HTTP redirect implementations
                    if ($client->strict_redirect()) {
                        $follow_method = $request->method();
                    } else {
                        $follow_method = Request::GET;
                    }
                    break;
            }

            // Prepare the additional request, copying any follow_headers that were present on the original request
            $orig_headers = $request->headers()->getArrayCopy();
            $follow_header_keys = array_intersect(array_keys($orig_headers), $client->follow_headers());
            $follow_headers = Arr::extract($orig_headers, $follow_header_keys);

            $follow_request = Request::factory($response->headers('Location'))
                ->method($follow_method)
                ->headers($follow_headers);

            if ($follow_method !== Request::GET) {
                $follow_request->body($request->body());
            }

            return $follow_request;
        }

        return null;
    }

}
