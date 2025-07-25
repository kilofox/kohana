<?php

/**
 * [Request_Client_External] HTTP driver performs external requests using the
 * php-http extension. To use this driver, ensure the following is completed
 * before executing an external request—ideally in the application bootstrap.
 *
 * @example
 *
 *       // In application bootstrap
 *       Request_Client_External::$client = 'Request_Client_HTTP';
 *
 * @package    Kohana
 * @category   Base
 * @author     Kohana Team
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    https://kohana.top/license
 * @uses       [PECL HTTP](https://wiki.php.net/rfc/pecl_http)
 */
class Kohana_Request_Client_HTTP extends Request_Client_External
{
    /**
     * Creates a new `Request_Client` object,
     * allows for dependency injection.
     *
     * @param   array    $params Params
     * @throws  Request_Exception
     */
    public function __construct(array $params = [])
    {
        // Check that PECL HTTP supports requests
        if (!http_support(HTTP_SUPPORT_REQUESTS)) {
            throw new Request_Exception('Need HTTP request support!');
        }

        // Carry on
        parent::__construct($params);
    }

    /**
     * @var     array     curl options
     * @link    https://www.php.net/function.curl-setopt
     */
    protected $_options = [];

    /**
     * Sends the HTTP message [Request] to a remote server and processes
     * the response.
     *
     * @param Request $request response to send
     * @param Response $response
     * @return  Response
     * @throws Kohana_Exception
     * @throws Request_Exception
     */
    public function _send_message(Request $request, Response $response)
    {
        $http_method_mapping = [
            HTTP_Request::GET => HTTPRequest::METH_GET,
            HTTP_Request::HEAD => HTTPRequest::METH_HEAD,
            HTTP_Request::POST => HTTPRequest::METH_POST,
            HTTP_Request::PUT => HTTPRequest::METH_PUT,
            HTTP_Request::DELETE => HTTPRequest::METH_DELETE,
            HTTP_Request::OPTIONS => HTTPRequest::METH_OPTIONS,
            HTTP_Request::TRACE => HTTPRequest::METH_TRACE,
            HTTP_Request::CONNECT => HTTPRequest::METH_CONNECT,
        ];

        // Create an http request object
        $http_request = new HTTPRequest($request->uri(), $http_method_mapping[$request->method()]);

        if ($this->_options) {
            // Set custom options
            $http_request->setOptions($this->_options);
        }

        // Set headers
        $http_request->setHeaders($request->headers()->getArrayCopy());

        // Set cookies
        $http_request->setCookies($request->cookie());

        // Set query data (?foo=bar&bar=foo)
        $http_request->setQueryData($request->query());

        // Set the body
        if ($request->method() === HTTP_Request::PUT) {
            $http_request->addPutData($request->body());
        } else {
            $http_request->setBody($request->body());
        }

        try {
            $http_request->send();
        } catch (HTTPRequestException $e) {
            throw new Request_Exception($e->getMessage());
        } catch (HTTPMalformedHeaderException $e) {
            throw new Request_Exception($e->getMessage());
        } catch (HTTPEncodingException $e) {
            throw new Request_Exception($e->getMessage());
        }

        // Build the response
        $response->status($http_request->getResponseCode())
            ->headers($http_request->getResponseHeader())
            ->cookie($http_request->getResponseCookies())
            ->body($http_request->getResponseBody());

        return $response;
    }

}
