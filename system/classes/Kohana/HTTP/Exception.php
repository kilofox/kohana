<?php

abstract class Kohana_HTTP_Exception extends Kohana_Exception
{
    /**
     * Creates an HTTP_Exception of the specified type.
     *
     * @param int $code the http status code
     * @param string $message Status message, custom content to display with error
     * @param array|null $variables translation variables
     * @param Exception|null $previous
     * @return  HTTP_Exception
     */
    public static function factory(int $code, string $message = '', array $variables = null, Exception $previous = null): HTTP_Exception
    {
        $class = 'HTTP_Exception_' . $code;

        return new $class($message, $variables, $previous);
    }

    /**
     * @var  int        http status code
     */
    protected $_code = 0;

    /**
     * @var  Request    Request instance that triggered this exception.
     */
    protected $_request;

    /**
     * Creates a new translated exception.
     *
     *     throw new Kohana_Exception('Something went terrible wrong, :user', [':user' => $user]);
     *
     * @param string $message status message, custom content to display with error
     * @param array|null $variables translation variables
     * @param Exception|null $previous
     */
    public function __construct(string $message = '', array $variables = null, Exception $previous = null)
    {
        parent::__construct($message, $variables, $this->_code, $previous);
    }

    /**
     * Store the Request that triggered this exception.
     *
     * @param Request|null $request Request object that triggered this exception.
     * @return  Kohana_HTTP_Exception|Request
     */
    public function request(Request $request = null)
    {
        if ($request === null)
            return $this->_request;

        $this->_request = $request;

        return $this;
    }

    /**
     * Generate a Response for the current Exception
     *
     * @return Response
     * @throws Kohana_Exception
     * @uses   Kohana_Exception::response()
     */
    public function get_response(): Response
    {
        return Kohana_Exception::response($this);
    }

}
