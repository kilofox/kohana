<?php

/**
 * An HTTP Request specific interface that adds the methods required
 * by HTTP requests. Over and above [Kohana_HTTP_Interaction], this
 * interface provides method, uri, get and post methods.
 *
 * @package    Kohana
 * @category   HTTP
 * @author     Kohana Team
 * @since      3.1.0
 * @copyright  (c) 2008-2014 Kohana Team
 * @license    https://kohana.top/license
 */
interface Kohana_HTTP_Request extends HTTP_Message
{
    // HTTP Methods
    const GET = 'GET';
    const POST = 'POST';
    const PUT = 'PUT';
    const DELETE = 'DELETE';
    const HEAD = 'HEAD';
    const OPTIONS = 'OPTIONS';
    const TRACE = 'TRACE';
    const CONNECT = 'CONNECT';

    /**
     * Gets or sets the HTTP method. Usually GET, POST, PUT or DELETE in
     * traditional CRUD applications.
     *
     * @param string|null $method Method to use for this request
     * @return  mixed
     */
    public function method($method = null);

    /**
     * Gets the URI of this request.
     *
     * @return  string
     */
    public function uri();
    /**
     * Gets or sets HTTP query string.
     *
     * @param   mixed   $key    Key or key value pairs to set
     * @param string|null $value Value to set to a key
     * @return  mixed
     */
    public function query($key = null, $value = null);
    /**
     * Gets or sets HTTP POST parameters to the request.
     *
     * @param   mixed   $key   Key or key value pairs to set
     * @param string|null $value Value to set to a key
     * @return  mixed
     */
    public function post($key = null, $value = null);
}
