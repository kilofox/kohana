<?php

/**
 * Message logging with observer-based log writing.
 *
 * [!!] This class does not support extensions, only additional writers.
 *
 * @package    Kohana
 * @category   Logging
 * @author     Kohana Team
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    https://kohana.top/license
 */
class Kohana_Log
{
    // Log message levels - Windows users see PHP Bug #18090
    const EMERGENCY = LOG_EMERG;    // 0
    const ALERT = LOG_ALERT;    // 1
    const CRITICAL = LOG_CRIT;     // 2
    const ERROR = LOG_ERR;      // 3
    const WARNING = LOG_WARNING;  // 4
    const NOTICE = LOG_NOTICE;   // 5
    const INFO = LOG_INFO;     // 6
    const DEBUG = LOG_DEBUG;    // 7

    /**
     * @var bool immediately write when logs are added
     */
    public static $write_on_add = false;

    /**
     * @var  Log  Singleton instance container
     */
    protected static $_instance;

    /**
     * Get the singleton instance of this class and enable writing at shutdown.
     *
     *     $log = Log::instance();
     *
     * @return  Log
     */
    public static function instance()
    {
        if (Log::$_instance === null) {
            // Create a new instance
            Log::$_instance = new Log;

            // Write the logs at shutdown
            register_shutdown_function([Log::$_instance, 'write']);
        }

        return Log::$_instance;
    }

    /**
     * @var  array  list of added messages
     */
    protected $_messages = [];

    /**
     * @var  array  list of log writers
     */
    protected $_writers = [];

    /**
     * Attaches a log writer, and optionally limits the levels of messages that
     * will be written by the writer.
     *
     *     $log->attach($writer);
     *
     * @param   Log_Writer  $writer     instance
     * @param   mixed       $levels     array of messages levels to write OR max level to write
     * @param   int $min_level min level to write IF $levels is not an array
     * @return  Kohana_Log
     */
    public function attach(Log_Writer $writer, $levels = [], $min_level = 0)
    {
        if (!is_array($levels)) {
            $levels = range($min_level, $levels);
        }

        $this->_writers["$writer"] = [
            'object' => $writer,
            'levels' => $levels
        ];

        return $this;
    }

    /**
     * Detaches a log writer. The same writer object must be used.
     *
     *     $log->detach($writer);
     *
     * @param   Log_Writer  $writer instance
     * @return  Kohana_Log
     */
    public function detach(Log_Writer $writer)
    {
        // Remove the writer
        unset($this->_writers["$writer"]);

        return $this;
    }

    /**
     * Adds a message to the log. Replacement values must be passed in to be
     * replaced using [strtr](https://www.php.net/strtr).
     *
     *     $log->add(Log::ERROR, 'Could not locate user: :user', [
     *         ':user' => $username,
     *     ]);
     *
     * @param string $level level of message
     * @param string $message message body
     * @param array|null $values values to replace in the message
     * @param array|null $additional additional custom parameters to supply to the log writer
     * @return  Kohana_Log
     */
    public function add($level, $message, array $values = null, array $additional = null)
    {
        if ($values) {
            // Insert the values into the message
            $message = strtr($message, $values);
        }

        // Grab a copy of the trace
        if (isset($additional['exception'])) {
            $trace = $additional['exception']->getTrace();
        } else {
            // Older php version don't have 'DEBUG_BACKTRACE_IGNORE_ARGS', so manually remove the args from the backtrace
            if (!defined('DEBUG_BACKTRACE_IGNORE_ARGS')) {
                $trace = array_map(function ($item) {
                    unset($item['args']);
                    return $item;
                }, array_slice(debug_backtrace(false), 1));
            } else {
                $trace = array_slice(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), 1);
            }
        }

        if ($additional === null) {
            $additional = [];
        }

        // Create a new message
        $this->_messages[] = [
            'time' => time(),
            'level' => $level,
            'body' => $message,
            'trace' => $trace,
            'file' => $trace[0]['file'] ?? null,
            'line' => $trace[0]['line'] ?? null,
            'class' => $trace[0]['class'] ?? null,
            'function' => $trace[0]['function'] ?? null,
            'additional' => $additional,
        ];

        if (Log::$write_on_add) {
            // Write logs as they are added
            $this->write();
        }

        return $this;
    }

    /**
     * Write and clear all the messages.
     *
     *     $log->write();
     *
     * @return  void
     */
    public function write()
    {
        if (empty($this->_messages)) {
            // There is nothing to write, move along
            return;
        }

        // Import all messages locally
        $messages = $this->_messages;

        // Reset the messages array
        $this->_messages = [];

        foreach ($this->_writers as $writer) {
            if (empty($writer['levels'])) {
                // Write all the messages
                $writer['object']->write($messages);
            } else {
                // Filtered messages
                $filtered = [];

                foreach ($messages as $message) {
                    if (in_array($message['level'], $writer['levels'])) {
                        // Writer accepts this kind of message
                        $filtered[] = $message;
                    }
                }

                // Write the filtered messages
                $writer['object']->write($filtered);
            }
        }
    }

}
