<?php

/**
 * "Expected" HTTP exception class. Used for all [HTTP_Exception]'s where a standard
 * Kohana error page should never be shown.
 *
 * Eg [HTTP_Exception_301], [HTTP_Exception_302] etc
 *
 * @package    Kohana
 * @category   Exceptions
 * @author     Kohana Team
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    https://kohana.top/license
 */
abstract class Kohana_HTTP_Exception_Expected extends HTTP_Exception
{
    /**
     * @var  Response   Response Object
     */
    protected $_response;

    /**
     * Creates a new translated exception.
     *
     *     throw new Kohana_Exception('Something went terrible wrong, :user', [
     *         ':user' => $user
     *     ]);
     *
     * @param string $message status message, custom content to display with error
     * @param array $variables translation variables
     * @return  void
     * @throws Kohana_Exception
     */
    public function __construct($message = null, array $variables = null, Exception $previous = null)
    {
        parent::__construct($message, $variables, $previous);

        // Prepare our response object and set the correct status code.
        $this->_response = Response::factory()
            ->status($this->_code);
    }

    /**
     * Gets and sets headers to the [Response].
     *
     * @see     [Response::headers]
     * @param   mixed   $key
     * @param   string  $value
     * @return  mixed
     */
    public function headers($key = null, $value = null)
    {
        $result = $this->_response->headers($key, $value);

        if (!$result instanceof Response)
            return $result;

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
        return true;
    }

    /**
     * Generate a Response for the current Exception
     *
     * @return Response
     * @throws Kohana_Exception
     * @uses   Kohana_Exception::response()
     */
    public function get_response()
    {
        $this->check();

        return $this->_response;
    }

}
