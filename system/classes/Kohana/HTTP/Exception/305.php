<?php

class Kohana_HTTP_Exception_305 extends HTTP_Exception_Expected
{
    /**
     * @var int HTTP 305 Use Proxy
     */
    protected $_code = 305;

    /**
     * Specifies the proxy to replay this request via
     *
     * @param string|null $uri URI of the proxy
     */
    public function location(string $uri = null)
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
    public function check(): bool
    {
        if (($location = $this->headers('location')) === null)
            throw new Kohana_Exception('A \'location\' must be specified for a redirect');

        if (strpos($location, '://') === false)
            throw new Kohana_Exception('An absolute URI to the proxy server must be specified');

        return true;
    }

}
