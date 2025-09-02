<?php

class Kohana_HTTP_Exception_405 extends HTTP_Exception_Expected
{
    /**
     * @var int HTTP 405 Method Not Allowed
     */
    protected $_code = 405;

    /**
     * Specifies the list of allowed HTTP methods
     *
     * @param array|string $methods List of allowed methods
     */
    public function allowed($methods): Kohana_HTTP_Exception_405
    {
        if (is_array($methods)) {
            $methods = implode(',', $methods);
        }

        $this->headers('allow', $methods);

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
        if ($this->headers('allow') === null)
            throw new Kohana_Exception('A list of allowed methods must be specified');

        return true;
    }

}
