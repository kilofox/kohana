<?php

/**
 * [Request_Client_External] Curl driver performs external requests using the
 * php-curl extension. This is the default driver for all external requests.
 *
 * @package    Kohana
 * @category   Base
 * @author     Kohana Team
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    https://kohana.top/license
 * @uses       [PHP cURL](https://www.php.net/manual/en/book.curl.php)
 */
class Kohana_Request_Client_Curl extends Request_Client_External
{
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
        $options = [];

        // Set the request method
        $options = $this->_set_curl_request_method($request, $options);

        // Set the request body. This is perfectly legal in CURL even
        // if using a request other than POST. PUT does support this method
        // and DOES NOT require writing data to disk before putting it, if
        // reading the PHP docs you may have got that impression. SdF
        // This will also add a Content-Type: application/x-www-form-urlencoded header unless you override it
        if ($body = $request->body()) {
            $options[CURLOPT_POSTFIELDS] = $body;
        }

        // Process headers
        if ($headers = $request->headers()) {
            $http_headers = [];

            foreach ($headers as $key => $value) {
                $http_headers[] = $key . ': ' . $value;
            }

            $options[CURLOPT_HTTPHEADER] = $http_headers;
        }

        // Process cookies
        if ($cookies = $request->cookie()) {
            $options[CURLOPT_COOKIE] = http_build_query($cookies, null, '; ');
        }

        // Get any existing response headers
        $response_header = $response->headers();

        // Implement the standard parsing parameters
        $options[CURLOPT_HEADERFUNCTION] = [$response_header, 'parse_header_string'];
        $this->_options[CURLOPT_RETURNTRANSFER] = true;
        $this->_options[CURLOPT_HEADER] = false;

        // Apply any additional options set to
        $options += $this->_options;

        $uri = $request->uri();

        if ($query = $request->query()) {
            $uri .= '?' . http_build_query($query, null, '&');
        }

        // Open a new remote connection
        $curl = curl_init($uri);

        // Set connection options
        if (!curl_setopt_array($curl, $options)) {
            throw new Request_Exception('Failed to set CURL options, check CURL documentation: :url', [':url' => 'https://www.php.net/curl_setopt_array']);
        }

        // Get the response body
        $body = curl_exec($curl);

        // Get the response information
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($body === false) {
            $error = curl_error($curl);
        }

        // Close the connection
        curl_close($curl);

        if (isset($error)) {
            throw new Request_Exception('Error fetching remote :url [ status :code ] :error', [':url' => $request->url(), ':code' => $code, ':error' => $error]);
        }

        $response->status($code)
            ->body($body);

        return $response;
    }

    /**
     * Sets the appropriate curl request options. Uses the responding option
     * for POST or CURLOPT_CUSTOMREQUEST otherwise
     *
     * @param Request $request
     * @param array $options
     * @return array
     */
    public function _set_curl_request_method(Request $request, array $options)
    {
        switch ($request->method()) {
            case Request::POST:
                $options[CURLOPT_POST] = true;
                break;
            default:
                $options[CURLOPT_CUSTOMREQUEST] = $request->method();
                break;
        }
        return $options;
    }

}
