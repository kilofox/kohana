<?php

/**
 * Kohana exception class. Translates exceptions using the [I18n] class.
 *
 * @package    Kohana
 * @category   Exceptions
 * @author     Kohana Team
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    https://kohana.top/license
 */
class Kohana_Kohana_Exception extends Exception
{
    /**
     * @var  array  PHP error code => human-readable name
     */
    public static $php_errors = [
        E_ERROR => 'Fatal Error',
        E_USER_ERROR => 'User Error',
        E_PARSE => 'Parse Error',
        E_WARNING => 'Warning',
        E_USER_WARNING => 'User Warning',
        E_STRICT => 'Strict',
        E_NOTICE => 'Notice',
        E_RECOVERABLE_ERROR => 'Recoverable Error',
        E_DEPRECATED => 'Deprecated',
    ];

    /**
     * @var  string  error rendering view
     */
    public static $error_view = 'kohana/error';

    /**
     * @var  string  error view content type
     */
    public static $error_view_content_type = 'text/html';

    /**
     * Creates a new translated exception.
     *
     *     throw new Kohana_Exception('Something went terrible wrong, :user', [':user' => $user]);
     *
     * @param string $message error message
     * @param array|null $variables translation variables
     * @param int|string $code the exception code
     * @param Exception|null $previous Previous exception
     */
    public function __construct($message = "", array $variables = null, $code = 0, Exception $previous = null)
    {
        // Set the message
        $message = __($message, $variables);

        // Pass the message and integer code to the parent
        parent::__construct($message, (int) $code, $previous);

        // Save the unmodified code
        // @link http://bugs.php.net/39615
        $this->code = $code;
    }

    /**
     * Magic object-to-string method.
     *
     *     echo $exception;
     *
     * @uses    Kohana_Exception::text
     * @return  string
     */
    public function __toString()
    {
        return Kohana_Exception::text($this);
    }

    /**
     * Inline exception handler, displays the error message, source of the
     * exception, and the stack trace of the error.
     *
     * @param Throwable $e
     * @return  void
     * @throws Kohana_Exception
     * @uses    Kohana_Exception::response
     */
    public static function handler($e)
    {
        $response = Kohana_Exception::_handler($e);

        // Send the response to the browser
        echo $response->send_headers()->body();

        exit(1);
    }

    /**
     * Exception handler, logs the exception and generates a Response object
     * for display.
     *
     * @uses    Kohana_Exception::response
     * @param   Throwable   $e
     * @return  Response
     */
    public static function _handler($e)
    {
        try {
            // Log the exception
            Kohana_Exception::log($e);

            // Generate the response
            return Kohana_Exception::response($e);
        } catch (Exception $e) {
            /**
             * Things are going *really* badly for us, We now have no choice
             * but to bail. Hard.
             */
            // Clean the output buffer if one exists
            ob_get_level() and ob_clean();

            // Set the Status code to 500, and Content-Type to text/plain.
            header('Content-Type: text/plain; charset=' . Kohana::$charset, true, 500);

            echo Kohana_Exception::text($e);

            exit(1);
        }
    }

    /**
     * Logs an exception.
     *
     * @uses    Kohana_Exception::text
     * @param   Throwable   $e
     * @param   int        $level
     * @return  void
     */
    public static function log($e, $level = Log::EMERGENCY)
    {
        if (is_object(Kohana::$log)) {
            // Create a text version of the exception
            $error = Kohana_Exception::text($e);

            // Add this exception to the log
            Kohana::$log->add($level, $error, null, ['exception' => $e]);

            // Make sure the logs are written
            Kohana::$log->write();
        }
    }

    /**
     * Get a single line of text representing the exception:
     *
     * Error [ Code ]: Message ~ File [ Line ]
     *
     * @param   Throwable   $e
     * @return  string
     */
    public static function text($e)
    {
        return sprintf('%s [ %s ]: %s ~ %s [ %d ]', get_class($e), $e->getCode(), strip_tags($e->getMessage()), Debug::path($e->getFile()), $e->getLine());
    }

    /**
     * Get a Response object representing the exception
     *
     * @param Throwable $e
     * @return  Response
     * @throws Kohana_Exception
     * @uses    Kohana_Exception::text
     */
    public static function response($e)
    {
        try {
            // Get the exception information
            $class = get_class($e);
            $code = $e->getCode();
            $message = $e->getMessage();
            $file = $e->getFile();
            $line = $e->getLine();
            $trace = $e->getTrace();

            /**
             * HTTP_Exceptions are constructed in the HTTP_Exception::factory()
             * method. We need to remove that entry from the trace and overwrite
             * the variables from above.
             */
            if ($e instanceof HTTP_Exception && $trace[0]['function'] === 'factory') {
                extract(array_shift($trace));
            }


            if ($e instanceof ErrorException) {
                /**
                 * If XDebug is installed, and this is a fatal error,
                 * use XDebug to generate the stack trace
                 */
                if (function_exists('xdebug_get_function_stack') && $code === E_ERROR) {
                    $trace = array_slice(array_reverse(xdebug_get_function_stack()), 4);

                    foreach ($trace as & $frame) {
                        /**
                         * XDebug pre 2.1.1 doesn't currently set the call type key
                         * http://bugs.xdebug.org/view.php?id=695
                         */
                        if (!isset($frame['type'])) {
                            $frame['type'] = '??';
                        }

                        // Xdebug returns the words 'dynamic' and 'static' instead of using '->' and '::' symbols
                        if ('dynamic' === $frame['type']) {
                            $frame['type'] = '->';
                        } elseif ('static' === $frame['type']) {
                            $frame['type'] = '::';
                        }

                        // XDebug also has a different name for the parameters array
                        if (isset($frame['params']) && !isset($frame['args'])) {
                            $frame['args'] = $frame['params'];
                        }
                    }
                }

                if (isset(Kohana_Exception::$php_errors[$code])) {
                    // Use the human-readable error name
                    $code = Kohana_Exception::$php_errors[$code];
                }
            }

            /**
             * The stack trace becomes unmanageable inside PHPUnit.
             *
             * The error view ends up several GB in size, taking
             * several minutes to render.
             */
            if (
                defined('PHPUnit_MAIN_METHOD')
                || defined('PHPUNIT_COMPOSER_INSTALL')
                || defined('__PHPUNIT_PHAR__')
            ) {
                $trace = array_slice($trace, 0, 2);
            }

            // Instantiate the error view.
            $view = View::factory(Kohana_Exception::$error_view, get_defined_vars());

            // Prepare the response object.
            $response = Response::factory();

            // Set the response status
            $response->status($e instanceof HTTP_Exception ? $e->getCode() : 500);

            // Set the response headers
            $response->headers('Content-Type', Kohana_Exception::$error_view_content_type . '; charset=' . Kohana::$charset);

            // Set the response body
            $response->body($view->render());
        } catch (Exception $e) {
            /**
             * Things are going badly for us, Lets try to keep things under control by
             * generating a simpler response object.
             */
            $response = Response::factory();
            $response->status(500);
            $response->headers('Content-Type', 'text/plain');
            $response->body(Kohana_Exception::text($e));
        }

        return $response;
    }

}
