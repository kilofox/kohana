<?php

class Kohana_HTTP_Exception_305 extends HTTP_Exception_Expected
{
    /**
     * @var   integer    HTTP 305 Use Proxy
     */
    protected $_code = 305;

    /**
     * Specifies the proxy to replay this request via
     *
     * @param  string  $location  URI of the proxy
     */
    public function location($uri = null)
    {
        if ($uri === null)
            return $this->headers('Location');

        $this->headers('Location', $uri);

        return $this;
    }

    /**
     * Validate this exception contains everything needed to continue.
     *
     * @throws Kohana_Exception
     * @return bool
     */
    public function check()
    {
        if (($location = $this->headers('location')) === null)
            throw new Kohana_Exception('A \'location\' must be specified for a redirect');

        if (strpos($location, '://') === false)
            throw new Kohana_Exception('An absolute URI to the proxy server must be specified');

        return true;
    }

}
