<?php

/**
 * Response wrapper. Created as the result of any [Request] execution
 * or utility method (i.e. Redirect). Implements standard HTTP
 * response format.
 *
 * @package    Kohana
 * @category   Base
 * @author     Kohana Team
 * @copyright  (c) 2008-2014 Kohana Team
 * @license    https://kohana.top/license
 * @since      3.1.0
 */
class Kohana_Response implements HTTP_Response
{
    /**
     * Factory method to create a new [Response]. Pass properties
     * in using an associative array.
     *
     *      // Create a new response
     *      $response = Response::factory();
     *
     *      // Create a new response with headers
     *      $response = Response::factory(['status' => 200]);
     *
     * @param   array    $config Set up the response object
     * @return  Response
     */
    public static function factory(array $config = [])
    {
        return new Response($config);
    }

    // HTTP status codes and messages
    public static $messages = [
        // Informational 1xx
        100 => 'Continue',
        101 => 'Switching Protocols',
        // Success 2xx
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        // Redirection 3xx
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found', // 1.1
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        // 306 is deprecated but reserved
        307 => 'Temporary Redirect',
        // Client Error 4xx
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        // Server Error 5xx
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        509 => 'Bandwidth Limit Exceeded'
    ];

    /**
     * @var int The response http status
     */
    protected $_status = 200;

    /**
     * @var  HTTP_Header  Headers returned in the response
     */
    protected $_header;

    /**
     * @var  string      The response body
     */
    protected $_body = '';

    /**
     * @var  array       Cookies to be returned in the response
     */
    protected $_cookies = [];

    /**
     * @var  string      The response protocol
     */
    protected $_protocol;

    /**
     * Sets up the response object
     *
     * @param   array $config Set up the response object
     * @return  void
     */
    public function __construct(array $config = [])
    {
        $this->_header = new HTTP_Header;

        foreach ($config as $key => $value) {
            if (property_exists($this, $key)) {
                if ($key === '_header') {
                    $this->headers($value);
                } else {
                    $this->$key = $value;
                }
            }
        }
    }

    /**
     * Outputs the body when cast to string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->_body;
    }

    /**
     * Gets or sets the body of the response
     *
     * @return Kohana_Response|string
     */
    public function body($content = null)
    {
        if ($content === null)
            return $this->_body;

        $this->_body = (string) $content;
        return $this;
    }

    /**
     * Gets or sets the HTTP protocol. The standard protocol to use
     * is `HTTP/1.1`.
     *
     * @param   string   $protocol Protocol to set to the request/response
     * @return  Kohana_Response|string
     */
    public function protocol($protocol = null)
    {
        if ($protocol) {
            $this->_protocol = strtoupper($protocol);
            return $this;
        }

        if ($this->_protocol === null) {
            $this->_protocol = HTTP::$protocol;
        }

        return $this->_protocol;
    }

    /**
     * Sets or gets the HTTP status from this response.
     *
     *      // Set the HTTP status to 404 Not Found
     *      $response = Response::factory()
     *              ->status(404);
     *
     *      // Get the current status
     *      $status = $response->status();
     *
     * @param int $code Status to set to this response
     * @return int|Kohana_Response
     * @throws Kohana_Exception
     */
    public function status($code = null)
    {
        if ($code === null) {
            return $this->_status;
        } elseif (array_key_exists($code, Response::$messages)) {
            $this->_status = (int) $code;
            return $this;
        } else {
            throw new Kohana_Exception(__METHOD__ . ' unknown status value : :value', [':value' => $code]);
        }
    }

    /**
     * Gets and sets headers to the [Response], allowing chaining
     * of response methods. If chaining isn't required, direct
     * access to the property should be used instead.
     *
     *       // Get a header
     *       $accept = $response->headers('Content-Type');
     *
     *       // Set a header
     *       $response->headers('Content-Type', 'text/html');
     *
     *       // Get all headers
     *       $headers = $response->headers();
     *
     *       // Set multiple headers
     *       $response->headers(['Content-Type' => 'text/html', 'Cache-Control' => 'no-cache']);
     *
     * @param mixed $key
     * @param string $value
     * @return mixed
     */
    public function headers($key = null, $value = null)
    {
        if ($key === null) {
            return $this->_header;
        } elseif (is_array($key)) {
            $this->_header->exchangeArray($key);
            return $this;
        } elseif ($value === null) {
            return Arr::get($this->_header, $key);
        } else {
            $this->_header[$key] = $value;
            return $this;
        }
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
     * Set and get cookies values for this response.
     *
     *     // Get the cookies set to the response
     *     $cookies = $response->cookie();
     *
     *     // Set a cookie to the response
     *     $response->cookie('session', [
     *          'value' => $value,
     *          'expiration' => 12352234
     *     ]);
     *
     * @param   mixed   $key    cookie name, or array of cookie values
     * @param   string  $value  value to set to cookie
     * @return array|string|Kohana_Response
     */
    public function cookie($key = null, $value = null)
    {
        // Handle the get cookie calls
        if ($key === null)
            return $this->_cookies;
        elseif (!is_array($key) && !$value)
            return Arr::get($this->_cookies, $key);

        // Handle the set cookie calls
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->cookie($k, $v);
            }
        } else {
            if (!is_array($value)) {
                $value = [
                    'value' => $value,
                    'expiration' => Cookie::$expiration
                ];
            } elseif (!isset($value['expiration'])) {
                $value['expiration'] = Cookie::$expiration;
            }

            $this->_cookies[$key] = $value;
        }

        return $this;
    }

    /**
     * Deletes a cookie set to the response
     *
     * @param   string  $name
     * @return  Kohana_Response
     */
    public function delete_cookie($name)
    {
        unset($this->_cookies[$name]);
        return $this;
    }

    /**
     * Deletes all cookies from this response
     *
     * @return Kohana_Response
     */
    public function delete_cookies()
    {
        $this->_cookies = [];
        return $this;
    }

    /**
     * Sends the response status and all set headers.
     *
     * @param bool $replace replace existing headers
     * @param callable $callback function to handle header output
     * @return  mixed
     * @throws Kohana_Exception
     */
    public function send_headers($replace = false, $callback = null)
    {
        return $this->_header->send_headers($this, $replace, $callback);
    }

    /**
     * Send file download as the response. All execution will be halted when
     * this method is called! Use true for the filename to send the current
     * response as the file content. The third parameter allows the following
     * options to be set:
     *
     * Type      | Option    | Description                        | Default Value
     * ----------|-----------|------------------------------------|--------------
     * `boolean` | inline    | Display inline instead of download | `false`
     * `string`  | mime_type | Manual mime type                   | Automatic
     * `boolean` | delete    | Delete the file after sending      | `false`
     *
     * Download a file that already exists:
     *
     *     $request->send_file('media/packages/kohana.zip');
     *
     * Download generated content as a file:
     *
     *     $request->response($content);
     *     $request->send_file(true, $filename);
     *
     * [!!] No further processing can be done after this method is called!
     *
     * @param string $filename filename with path, or true for the current response
     * @param string|null $download downloaded file name
     * @param array|null $options additional options
     * @return  void
     * @throws Kohana_Exception
     * @uses    File::mime_by_ext
     * @uses    File::mime
     * @uses    Request::send_headers
     */
    public function send_file($filename, $download = null, array $options = null)
    {
        if (!empty($options['mime_type'])) {
            // The mime-type has been manually set
            $mime = $options['mime_type'];
        }

        if ($filename === true) {
            if (empty($download)) {
                throw new Kohana_Exception('Download name must be provided for streaming files');
            }

            // Temporary files will automatically be deleted
            $options['delete'] = false;

            if (!isset($mime)) {
                // Guess the mime using the file extension
                $mime = File::mime_by_ext(strtolower(pathinfo($download, PATHINFO_EXTENSION)));
            }

            // Force the data to be rendered if
            $file_data = $this->_body;

            // Get the content size
            $size = strlen($file_data);

            // Create a temporary file to hold the current response
            $file = tmpfile();

            // Write the current response into the file
            fwrite($file, $file_data);

            // File data is no longer needed
            unset($file_data);
        } else {
            // Get the complete file path
            $filename = realpath($filename);

            if (empty($download)) {
                // Use the file name as the download file name
                $download = pathinfo($filename, PATHINFO_BASENAME);
            }

            // Get the file size
            $size = filesize($filename);

            if (!isset($mime)) {
                // Get the mime type from the extension of the download file
                $mime = File::mime_by_ext(pathinfo($download, PATHINFO_EXTENSION));
            }

            // Open the file for reading
            $file = fopen($filename, 'rb');
        }

        if (!is_resource($file)) {
            throw new Kohana_Exception('Could not read file to send: :file', [
            ':file' => $download,
            ]);
        }

        // Inline or download?
        $disposition = empty($options['inline']) ? 'attachment' : 'inline';

        // Calculate byte range to download.
        list($start, $end) = $this->_calculate_byte_range($size);

        if (!empty($options['resumable'])) {
            if ($start > 0 || $end < $size - 1) {
                // Partial Content
                $this->_status = 206;
            }

            // Range of bytes being sent
            $this->_header['content-range'] = 'bytes ' . $start . '-' . $end . '/' . $size;
            $this->_header['accept-ranges'] = 'bytes';
        }

        // Set the headers for a download
        $this->_header['content-disposition'] = $disposition . '; filename="' . $download . '"';
        $this->_header['content-type'] = $mime;
        $this->_header['content-length'] = (string) ($end - $start + 1);

        if (Request::user_agent('browser') === 'Internet Explorer') {
            // Naturally, IE does not act like a real browser...
            if (Request::$initial->secure()) {
                // http://support.microsoft.com/kb/316431
                $this->_header['pragma'] = $this->_header['cache-control'] = 'public';
            }

            if (version_compare(Request::user_agent('version'), '8.0', '>=')) {
                // http://ajaxian.com/archives/ie-8-security
                $this->_header['x-content-type-options'] = 'nosniff';
            }
        }

        // Send all headers now
        $this->send_headers();

        while (ob_get_level()) {
            // Flush all output buffers
            ob_end_flush();
        }

        // Manually stop execution
        ignore_user_abort(true);

        if (!Kohana::$safe_mode) {
            // Keep the script running forever
            set_time_limit(0);
        }

        // Send data in 16kb blocks
        $block = 1024 * 16;

        fseek($file, $start);

        while (!feof($file) && ($pos = ftell($file)) <= $end) {
            if (connection_aborted())
                break;

            if ($pos + $block > $end) {
                // Don't read past the buffer.
                $block = $end - $pos + 1;
            }

            // Output a block of the file
            echo fread($file, $block);

            // Send the data now
            flush();
        }

        // Close the file
        fclose($file);

        if (!empty($options['delete'])) {
            try {
                // Attempt to remove the file
                unlink($filename);
            } catch (Exception $e) {
                // Create a text version of the exception
                $error = Kohana_Exception::text($e);

                if (is_object(Kohana::$log)) {
                    // Add this exception to the log
                    Kohana::$log->add(Log::ERROR, $error);

                    // Make sure the logs are written
                    Kohana::$log->write();
                }

                // Do NOT display the exception, it will corrupt the output!
            }
        }

        // Stop execution
        exit;
    }

    /**
     * Renders the HTTP_Interaction to a string, producing
     *
     *  - Protocol
     *  - Headers
     *  - Body
     *
     * @return  string
     */
    public function render()
    {
        if (!$this->_header->offsetExists('content-type')) {
            // Add the default Content-Type header if required
            $this->_header['content-type'] = Kohana::$content_type . '; charset=' . Kohana::$charset;
        }

        // Set the content length
        $this->headers('content-length', (string) $this->content_length());

        // If Kohana expose, set the user-agent
        if (Kohana::$expose) {
            $this->headers('user-agent', Kohana::version());
        }

        // Prepare cookies
        if ($this->_cookies) {
            if (extension_loaded('http')) {
                $cookies = version_compare(phpversion('http'), '2.0.0', '>=') ?
                    (string) new \http\Cookie($this->_cookies) :
                    http_build_cookie($this->_cookies);
            } else {
                $cookies = [];

                // Parse each
                foreach ($this->_cookies as $key => $value) {
                    $string = $key . '=' . $value['value'] . '; expires=' . date('l, d M Y H:i:s T', $value['expiration']);
                    $cookies[] = $string;
                }
            }

            // Create the cookie string
            $this->_header['set-cookie'] = $cookies;
        }

        $output = $this->_protocol . ' ' . $this->_status . ' ' . Response::$messages[$this->_status] . "\r\n";
        $output .= $this->_header;
        $output .= $this->_body;

        return $output;
    }

    /**
     * Generate ETag
     * Generates an ETag from the response ready to be returned
     *
     * @throws Request_Exception
     * @return String Generated ETag
     */
    public function generate_etag()
    {
        if ($this->_body === '') {
            throw new Request_Exception('No response yet associated with request - cannot auto generate resource ETag');
        }

        // Generate a unique hash for the response
        return '"' . sha1($this->render()) . '"';
    }

    /**
     * Parse the byte ranges from the HTTP_RANGE header used for
     * resumable downloads.
     *
     * @link   https://www.rfc-editor.org/rfc/rfc9110.html#name-byte-ranges
     * @return array|false
     */
    protected function _parse_byte_range()
    {
        if (!isset($_SERVER['HTTP_RANGE'])) {
            return false;
        }

        // TODO, speed this up with the use of string functions.
        preg_match_all('/(-?[0-9]++(?:-(?![0-9]++))?)(?:-?([0-9]++))?/', $_SERVER['HTTP_RANGE'], $matches, PREG_SET_ORDER);

        return $matches[0];
    }

    /**
     * Calculates the byte range to use with send_file. If HTTP_RANGE doesn't
     * exist then the complete byte range is returned
     *
     * @param  int $size
     * @return array
     */
    protected function _calculate_byte_range($size)
    {
        // Defaults to start with when the HTTP_RANGE header doesn't exist.
        $start = 0;
        $end = $size - 1;

        if ($range = $this->_parse_byte_range()) {
            // We have a byte range from HTTP_RANGE
            $start = $range[1];

            if ($start[0] === '-') {
                // A negative value means we start from the end, so -500 would be the
                // last 500 bytes.
                $start = $size - abs($start);
            }

            if (isset($range[2])) {
                // Set the end range
                $end = $range[2];
            }
        }

        // Normalize values.
        $start = abs(intval($start));

        // Keep the end value in bounds and normalize it.
        $end = min(abs(intval($end)), $size - 1);

        // Keep the start in bounds.
        $start = $end < $start ? 0 : max($start, 0);

        return [$start, $end];
    }

}
