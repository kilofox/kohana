<?php

/**
 * Request. Uses the [Route] class to determine what
 * [Controller] to send the request to.
 *
 * @package    Kohana
 * @category   Base
 * @author     Kohana Team
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    https://kohana.top/license
 */
class Kohana_Request implements HTTP_Request
{
    /**
     * @var  string  client user agent
     */
    public static $user_agent = '';

    /**
     * @var  string  client IP address
     */
    public static $client_ip = '0.0.0.0';

    /**
     * @var  string  trusted proxy server IPs
     */
    public static $trusted_proxies = ['127.0.0.1', 'localhost', 'localhost.localdomain'];

    /**
     * @var  Request  main request instance
     */
    public static $initial;

    /**
     * @var  Request  currently executing request instance
     */
    public static $current;

    /**
     * Creates a new request object for the given URI. New requests should be
     * Created using the [Request::factory] method.
     *
     *     $request = Request::factory($uri);
     *
     * If $cache parameter is set, the response for the request will attempt to
     * be retrieved from the cache.
     *
     * @param bool $uri URI of the request
     * @param array $client_params An array of params to pass to the request client
     * @param bool $allow_external Allow external requests? (deprecated in 3.3)
     * @param array $injected_routes An array of routes to use, for testing
     * @return Request
     * @throws Kohana_Exception
     * @throws Request_Exception
     * @uses    Route::all
     * @uses    Route::matches
     */
    public static function factory($uri = true, $client_params = [], $allow_external = true, $injected_routes = [])
    {
        // If this is the initial request
        if (!Request::$initial) {
            $protocol = HTTP::$protocol;

            if (isset($_SERVER['REQUEST_METHOD'])) {
                // Use the server request method
                $method = $_SERVER['REQUEST_METHOD'];
            } else {
                // Default to GET requests
                $method = HTTP_Request::GET;
            }

            if (
                !empty($_SERVER['HTTPS']) && filter_var($_SERVER['HTTPS'], FILTER_VALIDATE_BOOLEAN)
                ||
                isset($_SERVER['HTTP_X_FORWARDED_PROTO'])
                && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https'
                && in_array($_SERVER['REMOTE_ADDR'], Request::$trusted_proxies)
            ) {
                // This request is secure
                $secure = true;
            }

            if (isset($_SERVER['HTTP_REFERER'])) {
                // There is a referrer for this request
                $referrer = $_SERVER['HTTP_REFERER'];
            }

            if (isset($_SERVER['HTTP_USER_AGENT'])) {
                // Browser type
                Request::$user_agent = $_SERVER['HTTP_USER_AGENT'];
            }

            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                // Typically used to denote AJAX requests
                $requested_with = $_SERVER['HTTP_X_REQUESTED_WITH'];
            }

            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])
                && isset($_SERVER['REMOTE_ADDR'])
                && in_array($_SERVER['REMOTE_ADDR'], Request::$trusted_proxies)) {
                // Use the forwarded IP address, typically set when the
                // client is using a proxy server.
                // Format: "X-Forwarded-For: client1, proxy1, proxy2"
                $client_ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);

                Request::$client_ip = array_shift($client_ips);

                unset($client_ips);
            } elseif (isset($_SERVER['HTTP_CLIENT_IP'])
                && isset($_SERVER['REMOTE_ADDR'])
                && in_array($_SERVER['REMOTE_ADDR'], Request::$trusted_proxies)) {
                // Use the forwarded IP address, typically set when the
                // client is using a proxy server.
                $client_ips = explode(',', $_SERVER['HTTP_CLIENT_IP']);

                Request::$client_ip = array_shift($client_ips);

                unset($client_ips);
            } elseif (isset($_SERVER['REMOTE_ADDR'])) {
                // The remote IP address
                Request::$client_ip = $_SERVER['REMOTE_ADDR'];
            }

            if ($method !== HTTP_Request::GET) {
                // Ensure the raw body is saved for future use
                $body = file_get_contents('php://input');
            }

            if ($uri === true) {
                // Attempt to guess the proper URI
                $uri = Request::detect_uri();
            }

            $cookies = [];

            if ($cookie_keys = array_keys($_COOKIE)) {
                foreach ($cookie_keys as $key) {
                    $cookies[$key] = Cookie::get($key);
                }
            }

            // Create the instance singleton
            Request::$initial = $request = new Request($uri, $client_params, $allow_external, $injected_routes);

            // Store global GET and POST data in the initial request only
            $request->protocol($protocol)
                ->query($_GET)
                ->post($_POST);

            if (isset($secure)) {
                // Set the request security
                $request->secure($secure);
            }

            // Set the request method
            $request->method($method);

            if (isset($referrer)) {
                // Set the referrer
                $request->referrer($referrer);
            }

            if (isset($requested_with)) {
                // Apply the requested with variable
                $request->requested_with($requested_with);
            }

            if (isset($body)) {
                // Set the request body (probably a PUT type)
                $request->body($body);
            }

            if (isset($cookies)) {
                $request->cookie($cookies);
            }
        } else {
            $request = new Request($uri, $client_params, $allow_external, $injected_routes);
        }

        return $request;
    }

    /**
     * Automatically detects the URI of the main request using PATH_INFO,
     * REQUEST_URI, PHP_SELF or REDIRECT_URL.
     *
     *     $uri = Request::detect_uri();
     *
     * @return  string  URI of the main request
     * @throws  Kohana_Exception
     * @since   3.0.8
     */
    public static function detect_uri()
    {
        if (!empty($_SERVER['PATH_INFO'])) {
            // PATH_INFO does not contain the docroot or index
            $uri = $_SERVER['PATH_INFO'];
        } else {
            // REQUEST_URI and PHP_SELF include the docroot and index

            if (isset($_SERVER['REQUEST_URI'])) {
                /**
                 * We use REQUEST_URI as the fallback value. The reason
                 * for this is we might have a malformed URL such as:
                 *
                 *  http://localhost/http://example.com/judge.php
                 *
                 * which parse_url can't handle. So rather than leave
                 * empty-handed, we'll use this.
                 */
                $uri = $_SERVER['REQUEST_URI'];

                if ($request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)) {
                    // Valid URL path found, set it.
                    $uri = $request_uri;
                }

                // Decode the request URI
                $uri = rawurldecode($uri);
            } elseif (isset($_SERVER['PHP_SELF'])) {
                $uri = $_SERVER['PHP_SELF'];
            } elseif (isset($_SERVER['REDIRECT_URL'])) {
                $uri = $_SERVER['REDIRECT_URL'];
            } else {
                // If you ever see this error, please report an issue at http://dev.kohanaphp.com/projects/kohana3/issues
                // along with any relevant information about your web server setup. Thanks!
                throw new Kohana_Exception('Unable to detect the URI using PATH_INFO, REQUEST_URI, PHP_SELF or REDIRECT_URL');
            }

            // Get the path from the base URL, including the index file
            $base_url = parse_url(Kohana::$base_url, PHP_URL_PATH);

            if (strpos($uri, $base_url) === 0) {
                // Remove the base URL from the URI
                $uri = (string) substr($uri, strlen($base_url));
            }

            if (Kohana::$index_file && strpos($uri, Kohana::$index_file) === 0) {
                // Remove the index file from the URI
                $uri = (string) substr($uri, strlen(Kohana::$index_file));
            }
        }

        return $uri;
    }

    /**
     * Return the currently executing request. This is changed to the current
     * request when [Request::execute] is called and restored when the request
     * is completed.
     *
     *     $request = Request::current();
     *
     * @return  Request
     * @since   3.0.5
     */
    public static function current()
    {
        return Request::$current;
    }

    /**
     * Returns the first request encountered by this framework. This will should
     * only be set once during the first [Request::factory] invocation.
     *
     *     // Get the first request
     *     $request = Request::initial();
     *
     *     // Test whether the current request is the first request
     *     if (Request::initial() === Request::current())
     *          // Do something useful
     *
     * @return  Request
     * @since   3.1.0
     */
    public static function initial()
    {
        return Request::$initial;
    }

    /**
     * Returns information about the initial user agent.
     *
     * @param mixed $value array or string to return: browser, version, robot, mobile, platform
     * @return  mixed   requested information, false if nothing is found
     * @throws Kohana_Exception
     * @uses    Text::user_agent
     * @uses    Request::$user_agent
     */
    public static function user_agent($value)
    {
        return Text::user_agent(Request::$user_agent, $value);
    }

    /**
     * Returns the accepted content types. If a specific type is defined,
     * the quality of that type will be returned.
     *
     *     $types = Request::accept_type();
     *
     * [!!] Deprecated in favor of using [HTTP_Header::accepts_at_quality].
     *
     * @deprecated  since version 3.3.0
     * @param   string  $type Content MIME type
     * @return  mixed   An array of all types or a specific type as a string
     * @uses    Request::_parse_accept
     */
    public static function accept_type($type = null)
    {
        static $accepts;

        if ($accepts === null) {
            // Parse the HTTP_ACCEPT header
            $accepts = Request::_parse_accept($_SERVER['HTTP_ACCEPT'], ['*/*' => 1.0]);
        }

        if (isset($type)) {
            // Return the quality setting for this type
            return isset($accepts[$type]) ? $accepts[$type] : $accepts['*/*'];
        }

        return $accepts;
    }

    /**
     * Returns the accepted languages. If a specific language is defined,
     * the quality of that language will be returned. If the language is not
     * accepted, false will be returned.
     *
     *     $langs = Request::accept_lang();
     *
     * [!!] Deprecated in favor of using [HTTP_Header::accepts_language_at_quality].
     *
     * @deprecated  since version 3.3.0
     * @param   string  $lang  Language code
     * @return  mixed   An array of all types or a specific type as a string
     * @uses    Request::_parse_accept
     */
    public static function accept_lang($lang = null)
    {
        static $accepts;

        if ($accepts === null) {
            // Parse the HTTP_ACCEPT_LANGUAGE header
            $accepts = Request::_parse_accept($_SERVER['HTTP_ACCEPT_LANGUAGE']);
        }

        if (isset($lang)) {
            // Return the quality setting for this lang
            return isset($accepts[$lang]) ? $accepts[$lang] : false;
        }

        return $accepts;
    }

    /**
     * Returns the accepted encodings. If a specific encoding is defined,
     * the quality of that encoding will be returned. If the encoding is not
     * accepted, false will be returned.
     *
     *     $encodings = Request::accept_encoding();
     *
     * [!!] Deprecated in favor of using [HTTP_Header::accepts_encoding_at_quality].
     *
     * @deprecated  since version 3.3.0
     * @param   string  $type Encoding type
     * @return  mixed   An array of all types or a specific type as a string
     * @uses    Request::_parse_accept
     */
    public static function accept_encoding($type = null)
    {
        static $accepts;

        if ($accepts === null) {
            // Parse the HTTP_ACCEPT_LANGUAGE header
            $accepts = Request::_parse_accept($_SERVER['HTTP_ACCEPT_ENCODING']);
        }

        if (isset($type)) {
            // Return the quality setting for this type
            return isset($accepts[$type]) ? $accepts[$type] : false;
        }

        return $accepts;
    }

    /**
     * Determines if a file larger than the post_max_size has been uploaded. PHP
     * does not handle this situation gracefully on its own, so this method
     * helps to solve that problem.
     *
     * @return bool
     * @throws Kohana_Exception
     * @uses    Arr::get
     * @uses    Num::bytes
     */
    public static function post_max_size_exceeded()
    {
        // Make sure the request method is POST
        if (Request::$initial->method() !== HTTP_Request::POST)
            return false;

        // Get the post_max_size in bytes
        $max_bytes = Num::bytes(ini_get('post_max_size'));

        // Error occurred if method is POST, and content length is too long
        return Arr::get($_SERVER, 'CONTENT_LENGTH') > $max_bytes;
    }

    /**
     * Process a request to find a matching route
     *
     * @param Request $request Request
     * @param array $routes Route
     * @return  array
     */
    public static function process(Request $request, $routes = null)
    {
        // Load routes
        $routes = empty($routes) ? Route::all() : $routes;

        foreach ($routes as $route) {
            // Use external routes for reverse routing only
            if ($route->is_external()) {
                continue;
            }

            // We found something suitable
            if ($params = $route->matches($request)) {
                return [
                    'params' => $params,
                    'route' => $route,
                ];
            }
        }

        return null;
    }

    /**
     * Parses an Accept header and returns an array (type => quality) of the
     * accepted types, ordered by quality.
     *
     *     $accept = Request::_parse_accept($header, $defaults);
     *
     * @param string $header Header to parse
     * @param array|null $accepts Default values
     * @return  array
     */
    protected static function _parse_accept(& $header, array $accepts = null)
    {
        if (!empty($header)) {
            // Get all the types
            $types = explode(',', $header);

            foreach ($types as $type) {
                // Split the type into parts
                $parts = explode(';', $type);

                // Make the type only the MIME
                $type = trim(array_shift($parts));

                // Default quality is 1.0
                $quality = 1.0;

                foreach ($parts as $part) {
                    // Prevent undefined $value notice below
                    if (strpos($part, '=') === false)
                        continue;

                    // Separate the key and value
                    list ($key, $value) = explode('=', trim($part));

                    if ($key === 'q') {
                        // There is a quality for this type
                        $quality = (float) trim($value);
                    }
                }

                // Add the accept type and quality
                $accepts[$type] = $quality;
            }
        }

        // Make sure that accepts is an array
        $accepts = (array) $accepts;

        // Order by quality
        arsort($accepts);

        return $accepts;
    }

    /**
     * @var  string  the x-requested-with header which most likely
     *               will be xmlhttprequest
     */
    protected $_requested_with;

    /**
     * @var  string  method: GET, POST, PUT, DELETE, HEAD, etc
     */
    protected $_method = 'GET';

    /**
     * @var  string  protocol: HTTP/1.1, FTP, CLI, etc
     */
    protected $_protocol;

    /**
     * @var bool
     */
    protected $_secure = false;

    /**
     * @var  string  referring URL
     */
    protected $_referrer;

    /**
     * @var  Route       route matched for this request
     */
    protected $_route;

    /**
     * @var  Route       array of routes to manually look at instead of the global namespace
     */
    protected $_routes;

    /**
     * @var  Kohana_HTTP_Header  headers to sent as part of the request
     */
    protected $_header;

    /**
     * @var  string the body
     */
    protected $_body;

    /**
     * @var  string  controller directory
     */
    protected $_directory = '';

    /**
     * @var  string  controller to be executed
     */
    protected $_controller;

    /**
     * @var  string  action to be executed in the controller
     */
    protected $_action;

    /**
     * @var  string  the URI of the request
     */
    protected $_uri;

    /**
     * @var bool external request
     */
    protected $_external = false;

    /**
     * @var  array   parameters from the route
     */
    protected $_params = [];

    /**
     * @var array    query parameters
     */
    protected $_get = [];

    /**
     * @var array    post parameters
     */
    protected $_post = [];

    /**
     * @var array    cookies to send with the request
     */
    protected $_cookies = [];

    /**
     * @var Kohana_Request_Client
     */
    protected $_client;

    /**
     * Creates a new request object for the given URI. New requests should be
     * Created using the [Request::factory] method.
     *
     *     $request = new Request($uri);
     *
     * If $cache parameter is set, the response for the request will attempt to
     * be retrieved from the cache.
     *
     * @param   string  $uri              URI of the request
     * @param   array   $client_params    Array of params to pass to the request client
     * @param   bool    $allow_external   Allow external requests? (deprecated in 3.3)
     * @param   array   $injected_routes  An array of routes to use, for testing
     * @return  void
     * @throws  Request_Exception
     * @uses    Route::all
     * @uses    Route::matches
     */
    public function __construct($uri, $client_params = [], $allow_external = true, $injected_routes = [])
    {
        $client_params = is_array($client_params) ? $client_params : [];

        // Initialise the header
        $this->_header = new HTTP_Header([]);

        // Assign injected routes
        $this->_routes = $injected_routes;

        // Cleanse query parameters from URI (faster that parse_url())
        $split_uri = explode('?', $uri);
        $uri = array_shift($split_uri);

        if ($split_uri) {
            parse_str($split_uri[0], $this->_get);
        }

        // Detect protocol (if present)
        // $allow_external = false prevents the default index.php from
        // being able to proxy external pages.
        if (!$allow_external || strpos($uri, '://') === false) {
            // Remove leading and trailing slashes from the URI
            $this->_uri = trim($uri, '/');

            // Apply the client
            $this->_client = new Request_Client_Internal($client_params);
        } else {
            // Create a route
            $this->_route = new Route($uri);

            // Store the URI
            $this->_uri = $uri;

            // Set the security setting if required
            if (strpos($uri, 'https://') === 0) {
                $this->secure(true);
            }

            // Set external state
            $this->_external = true;

            // Set up the client
            $this->_client = Request_Client_External::factory($client_params);
        }
    }

    /**
     * Returns the response as the string representation of a request.
     *
     *     echo $request;
     *
     * @return  string
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * Sets and gets the URI from the request.
     *
     * @param   string $uri
     * @return  mixed
     */
    public function uri($uri = null)
    {
        if ($uri === null) {
            // Act as a getter
            return $this->_uri === '' ? '/' : $this->_uri;
        }

        // Act as a setter
        $this->_uri = $uri;

        return $this;
    }

    /**
     * Create a URL string from the current request. This is a shortcut for:
     *
     *     echo URL::site($this->request->uri(), $protocol);
     *
     * @param mixed $protocol protocol string or Request object
     * @return  string
     * @throws Kohana_Exception
     * @uses    URL::site
     * @since   3.0.7
     */
    public function url($protocol = null)
    {
        if ($this->is_external()) {
            // If it's an external request return the URI
            return $this->uri();
        }

        // Create a URI with the current route, convert to a URL and returns
        return URL::site($this->uri(), $protocol);
    }

    /**
     * Retrieves a value from the route parameters.
     *
     *     $id = $request->param('id');
     *
     * @param   string   $key      Key of the value
     * @param   mixed    $default  Default value if the key is not set
     * @return  mixed
     */
    public function param($key = null, $default = null)
    {
        if ($key === null) {
            // Return the full array
            return $this->_params;
        }

        return isset($this->_params[$key]) ? $this->_params[$key] : $default;
    }

    /**
     * Sets and gets the referrer from the request.
     *
     * @param   string $referrer
     * @return  Kohana_Request|string
     */
    public function referrer($referrer = null)
    {
        if ($referrer === null) {
            // Act as a getter
            return $this->_referrer;
        }

        // Act as a setter
        $this->_referrer = (string) $referrer;

        return $this;
    }

    /**
     * Sets and gets the route from the request.
     *
     * @param Route|null $route
     * @return Kohana_Request|Route
     */
    public function route(Route $route = null)
    {
        if ($route === null) {
            // Act as a getter
            return $this->_route;
        }

        // Act as a setter
        $this->_route = $route;

        return $this;
    }

    /**
     * Sets and gets the directory for the controller.
     *
     * @param   string   $directory  Directory to execute the controller from
     * @return  Kohana_Request|string
     */
    public function directory($directory = null)
    {
        if ($directory === null) {
            // Act as a getter
            return $this->_directory;
        }

        // Act as a setter
        $this->_directory = (string) $directory;

        return $this;
    }

    /**
     * Sets and gets the controller for the matched route.
     *
     * @param   string   $controller  Controller to execute the action
     * @return  Kohana_Request|string
     */
    public function controller($controller = null)
    {
        if ($controller === null) {
            // Act as a getter
            return $this->_controller;
        }

        // Act as a setter
        $this->_controller = (string) $controller;

        return $this;
    }

    /**
     * Sets and gets the action for the controller.
     *
     * @param   string   $action  Action to execute the controller from
     * @return  Kohana_Request|string
     */
    public function action($action = null)
    {
        if ($action === null) {
            // Act as a getter
            return $this->_action;
        }

        // Act as a setter
        $this->_action = (string) $action;

        return $this;
    }

    /**
     * Provides access to the [Request_Client].
     *
     * @return Kohana_Request|Request_Client
     */
    public function client(Request_Client $client = null)
    {
        if ($client === null)
            return $this->_client;
        else {
            $this->_client = $client;
            return $this;
        }
    }

    /**
     * Gets and sets the requested with property, which should
     * be relative to the x-requested-with pseudo header.
     *
     * @param   string    $requested_with Requested with value
     * @return  Kohana_Request|string
     */
    public function requested_with($requested_with = null)
    {
        if ($requested_with === null) {
            // Act as a getter
            return $this->_requested_with;
        }

        // Act as a setter
        $this->_requested_with = strtolower($requested_with);

        return $this;
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
     * @return  Response
     * @throws Kohana_Exception
     * @throws Request_Exception
     * @uses    [Kohana::$profiling]
     * @uses    [Profiler]
     */
    public function execute()
    {
        if (!$this->_external) {
            $processed = Request::process($this, $this->_routes);

            if ($processed) {
                // Store the matching route
                $this->_route = $processed['route'];
                $params = $processed['params'];

                // Is this route external?
                $this->_external = $this->_route->is_external();

                if (isset($params['directory'])) {
                    // Controllers are in a subdirectory
                    $this->_directory = $params['directory'];
                }

                // Store the controller
                $this->_controller = $params['controller'];

                // Store the action
                $this->_action = isset($params['action']) ? $params['action'] : Route::$default_action;

                // These are accessible as public vars and can be overloaded
                unset($params['controller'], $params['action'], $params['directory']);

                // Params cannot be changed once matched
                $this->_params = $params;
            }
        }

        if (!$this->_route instanceof Route) {
            return HTTP_Exception::factory(404, 'Unable to find a route to match the URI: :uri', [':uri' => $this->_uri])
                    ->request($this)
                    ->get_response();
        }

        if (!$this->_client instanceof Request_Client) {
            throw new Request_Exception('Unable to execute :uri without a Kohana_Request_Client', [':uri' => $this->_uri]);
        }

        return $this->_client->execute($this);
    }

    /**
     * Returns whether this request is the initial request Kohana received.
     * Can be used to test for sub requests.
     *
     *     if (!$request->is_initial())
     *         // This is a sub request
     *
     * @return bool
     */
    public function is_initial()
    {
        return $this === Request::$initial;
    }

    /**
     * Readonly access to the [Request::$_external] property.
     *
     *     if (!$request->is_external())
     *          // This is an internal request
     *
     * @return bool
     */
    public function is_external()
    {
        return $this->_external;
    }

    /**
     * Returns whether this is an ajax request (as used by JS frameworks)
     *
     * @return bool
     */
    public function is_ajax()
    {
        return $this->requested_with() === 'xmlhttprequest';
    }

    /**
     * Gets or sets the HTTP method. Usually GET, POST, PUT or DELETE in
     * traditional CRUD applications.
     *
     * @param   string   $method  Method to use for this request
     * @return  Kohana_Request|string
     */
    public function method($method = null)
    {
        if ($method === null) {
            // Act as a getter
            return $this->_method;
        }

        // Act as a setter
        $this->_method = strtoupper($method);

        return $this;
    }

    /**
     * Gets or sets the HTTP protocol. If there is no current protocol set,
     * it will use the default set in HTTP::$protocol
     *
     * @param   string   $protocol  Protocol to set to the request
     * @return  Kohana_Request|string
     */
    public function protocol($protocol = null)
    {
        if ($protocol === null) {
            if ($this->_protocol)
                return $this->_protocol;
            else
                return $this->_protocol = HTTP::$protocol;
        }

        // Act as a setter
        $this->_protocol = strtoupper($protocol);
        return $this;
    }

    /**
     * Getter/Setter to the security settings for this request. This
     * method should be treated as immutable.
     *
     * @param bool $secure is this request secure?
     * @return  bool|Kohana_Request
     */
    public function secure($secure = null)
    {
        if ($secure === null)
            return $this->_secure;

        // Act as a setter
        $this->_secure = (bool) $secure;
        return $this;
    }

    /**
     * Gets or sets HTTP headers oo the request. All headers
     * are included immediately after the HTTP protocol definition during
     * transmission. This method provides a simple array or key/value
     * interface to the headers.
     *
     * @param   mixed   $key   Key or array of key/value pairs to set
     * @param   string  $value Value to set to the supplied key
     * @return  mixed
     */
    public function headers($key = null, $value = null)
    {
        if ($key instanceof HTTP_Header) {
            // Act a setter, replace all headers
            $this->_header = $key;

            return $this;
        }

        if (is_array($key)) {
            // Act as a setter, replace all headers
            $this->_header->exchangeArray($key);

            return $this;
        }

        if ($this->_header->count() === 0 && $this->is_initial()) {
            // Lazy load the request headers
            $this->_header = HTTP::request_headers();
        }

        if ($key === null) {
            // Act as a getter, return all headers
            return $this->_header;
        } elseif ($value === null) {
            // Act as a getter, single header
            return $this->_header->offsetExists($key) ? $this->_header->offsetGet($key) : null;
        }

        // Act as a setter for a single header
        $this->_header[$key] = $value;

        return $this;
    }

    /**
     * Set and get cookies values for this request.
     *
     * @param   mixed    $key    Cookie name, or array of cookie values
     * @param   string   $value  Value to set to cookie
     * @return array|mixed|Kohana_Request
     */
    public function cookie($key = null, $value = null)
    {
        if (is_array($key)) {
            // Act as a setter, replace all cookies
            $this->_cookies = $key;
            return $this;
        } elseif ($key === null) {
            // Act as a getter, all cookies
            return $this->_cookies;
        } elseif ($value === null) {
            // Act as a getting, single cookie
            return isset($this->_cookies[$key]) ? $this->_cookies[$key] : null;
        }

        // Act as a setter for a single cookie
        $this->_cookies[$key] = (string) $value;

        return $this;
    }

    /**
     * Gets or sets the HTTP body of the request. The body is
     * included after the header, separated by a single empty new line.
     *
     * @param   string  $content Content to set to the object
     * @return  Kohana_Request|string
     */
    public function body($content = null)
    {
        if ($content === null) {
            // Act as a getter
            return $this->_body;
        }

        // Act as a setter
        $this->_body = $content;

        return $this;
    }

    /**
     * Returns the length of the body for use with
     * content header
     *
     * @return int
     */
    public function content_length()
    {
        return strlen($this->body());
    }

    /**
     * Renders the HTTP_Interaction to a string, producing
     *
     *  - Protocol
     *  - Headers
     *  - Body
     *
     *  If there are variables set to the `Kohana_Request::$_post`
     *  they will override any values set to body.
     *
     * @return  string
     */
    public function render()
    {
        if (!$post = $this->post()) {
            $body = $this->body();
        } else {
            $body = http_build_query($post, null, '&');
            $this->body($body)
                ->headers('content-type', 'application/x-www-form-urlencoded; charset=' . Kohana::$charset);
        }

        // Set the content length
        $this->headers('content-length', (string) $this->content_length());

        // If Kohana expose, set the user-agent
        if (Kohana::$expose) {
            $this->headers('user-agent', Kohana::version());
        }

        // Prepare cookies
        if ($this->_cookies) {
            $cookie_string = [];

            // Parse each
            foreach ($this->_cookies as $key => $value) {
                $cookie_string[] = $key . '=' . $value;
            }

            // Create the cookie string
            $this->_header['cookie'] = implode('; ', $cookie_string);
        }

        $output = $this->method() . ' ' . $this->uri() . ' ' . $this->protocol() . "\r\n";
        $output .= $this->_header;
        $output .= $body;

        return $output;
    }

    /**
     * Gets or sets HTTP query string.
     *
     * @param   mixed   $key    Key or key value pairs to set
     * @param   string  $value  Value to set to a key
     * @return  mixed
     * @uses    Arr::path
     */
    public function query($key = null, $value = null)
    {
        if (is_array($key)) {
            // Act as a setter, replace all query strings
            $this->_get = $key;

            return $this;
        }

        if ($key === null) {
            // Act as a getter, all query strings
            return $this->_get;
        } elseif ($value === null) {
            // Act as a getter, single query string
            return Arr::path($this->_get, $key);
        }

        // Act as a setter, single query string
        $this->_get[$key] = $value;

        return $this;
    }

    /**
     * Gets or sets HTTP POST parameters to the request.
     *
     * @param   mixed  $key    Key or key value pairs to set
     * @param   string $value  Value to set to a key
     * @return  mixed
     * @uses    Arr::path
     */
    public function post($key = null, $value = null)
    {
        if (is_array($key)) {
            // Act as a setter, replace all fields
            $this->_post = $key;

            return $this;
        }

        if ($key === null) {
            // Act as a getter, all fields
            return $this->_post;
        } elseif ($value === null) {
            // Act as a getter, single field
            return Arr::path($this->_post, $key);
        }

        // Act as a setter, single field
        $this->_post[$key] = $value;

        return $this;
    }

}
