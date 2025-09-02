<?php

/**
 * [Request_Client_External] Stream driver performs external requests using php
 * sockets. To use this driver, ensure the following is completed
 * before executing an external request—ideally in the application bootstrap.
 *
 * @example
 *
 *       // In application bootstrap
 *       Request_Client_External::$client = 'Request_Client_Stream';
 *
 * @package    Kohana
 * @category   Base
 * @author     Kohana Team
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    https://kohana.top/license
 * @uses       [PHP Streams](https://www.php.net/manual/en/book.stream.php)
 */
class Kohana_Request_Client_Stream extends Request_Client_External
{
    /**
     * Sends the HTTP message [Request] to a remote server and processes
     * the response.
     *
     * @param Request $request request to send
     * @param Response $response response to send
     * @return  Response
     * @throws Kohana_Exception
     * @uses    [PHP cURL](https://www.php.net/manual/en/book.curl.php)
     */
    public function _send_message(Request $request, Response $response): Response
    {
        // Calculate stream mode
        $mode = $request->method() === HTTP_Request::GET ? 'r' : 'r+';

        // Process cookies
        if ($cookies = $request->cookie()) {
            $request->headers('cookie', http_build_query($cookies, null, '; '));
        }

        // Get the message body
        $body = $request->body();

        if (is_resource($body)) {
            $body = stream_get_contents($body);
        }

        // Set the content length
        $request->headers('content-length', (string) strlen($body));

        list($protocol) = explode('/', $request->protocol());

        // Create the context
        $options = [
            strtolower($protocol) => [
                'method' => $request->method(),
                'header' => (string) $request->headers(),
                'content' => $body
            ]
        ];

        // Create the context stream
        $context = stream_context_create($options);

        stream_context_set_option($context, $this->_options);

        $uri = $request->uri();

        if ($query = $request->query()) {
            $uri .= '?' . http_build_query($query, null, '&');
        }

        $stream = fopen($uri, $mode, false, $context);

        $meta_data = stream_get_meta_data($stream);

        // Get the HTTP response code
        $http_response = array_shift($meta_data['wrapper_data']);

        if (preg_match_all('/(\w+\/\d\.\d) (\d{3})/', $http_response, $matches) !== false) {
            $protocol = $matches[1][0];
            $status = (int) $matches[2][0];
        } else {
            $protocol = null;
            $status = null;
        }

        // Get any existing response headers
        $response_header = $response->headers();

        // Process headers
        array_map([$response_header, 'parse_header_string'], [], $meta_data['wrapper_data']);

        $response->status($status)
            ->protocol($protocol)
            ->body(stream_get_contents($stream));

        // Close the stream after use
        fclose($stream);

        return $response;
    }

}
