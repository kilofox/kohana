<?php

/**
 * Contains debugging and dumping tools.
 *
 * @package    Kohana
 * @category   Base
 * @author     Kohana Team
 * @copyright  (c) 2008-2014 Kohana Team
 * @license    https://kohana.top/license
 */
class Kohana_Debug
{
    /**
     * Returns an HTML string of debugging information about any number of
     * variables, each wrapped in a "pre" tag:
     *
     *     // Displays the type and value of each variable
     *     echo Debug::vars($foo, $bar, $baz);
     *
     * @param mixed ...$variables variable to debug
     * @return  string
     */
    public static function vars(...$variables)
    {
        if (empty($variables)) {
            return '';
        }

        $output = [];
        foreach ($variables as $var) {
            $output[] = Debug::_dump($var, 1024);
        }

        return '<pre class="debug">' . implode("\n", $output) . '</pre>';
    }

    /**
     * Returns an HTML string of information about a single variable.
     *
     * Borrows heavily on concepts from the Debug class of [Nette](http://nettephp.com/).
     *
     * @param   mixed   $value              variable to dump
     * @param   int $length maximum length of strings
     * @param   int $level_recursion recursion limit
     * @return  string
     */
    public static function dump($value, $length = 128, $level_recursion = 10)
    {
        return Debug::_dump($value, $length, $level_recursion);
    }

    /**
     * Helper for Debug::dump(), handles recursion in arrays and objects.
     *
     * @param   mixed   $var    variable to dump
     * @param   int $length maximum length of strings
     * @param   int $limit recursion limit
     * @param   int $level current recursion level (internal usage only!)
     * @return  string
     */
    protected static function _dump(& $var, $length = 128, $limit = 10, $level = 0)
    {
        if ($var === null) {
            return '<small>NULL</small>';
        }

        if (is_bool($var)) {
            return '<small>bool</small> ' . ($var ? 'TRUE' : 'FALSE');
        }

        if (is_float($var)) {
            return '<small>float</small> ' . $var;
        }

        if (is_resource($var)) {
            if (($type = get_resource_type($var)) === 'stream') {
                $meta = stream_get_meta_data($var);

                if (isset($meta['uri'])) {
                    $file = $meta['uri'];

                    if (function_exists('stream_is_local')) {
                        // Only exists on PHP >= 5.2.4
                        if (stream_is_local($file)) {
                            $file = Debug::path($file);
                        }
                    }

                    return '<small>resource</small><span>(' . $type . ')</span> ' . htmlspecialchars($file, ENT_NOQUOTES, Kohana::$charset);
                }
            }

            return '<small>resource</small><span>(' . $type . ')</span>';
        }

        if (is_string($var)) {
            // Clean invalid multibyte characters. iconv is only invoked
            // if there are non ASCII characters in the string, so this
            // isn't too much of a hit.
            $var = UTF8::clean($var, Kohana::$charset);

            if (UTF8::strlen($var) > $length) {
                // Encode the truncated string
                $str = htmlspecialchars(UTF8::substr($var, 0, $length), ENT_NOQUOTES, Kohana::$charset) . '&nbsp;&hellip;';
            } else {
                // Encode the string
                $str = htmlspecialchars($var, ENT_NOQUOTES, Kohana::$charset);
            }

            return '<small>string</small><span>(' . strlen($var) . ')</span> "' . $str . '"';
        }

        if (is_array($var)) {
            $output = [];

            // Indentation for this variable
            $space = str_repeat($s = '    ', $level);

            static $marker;

            if ($marker === null) {
                // Make a unique marker - force it to be alphanumeric so that it is always treated as a string array key
                $marker = uniqid("\x00") . "x";
            }

            if (empty($var)) {
                // Do nothing
            } elseif (isset($var[$marker])) {
                $output[] = "(\n$space$s*RECURSION*\n$space)";
            } elseif ($level < $limit) {
                $output[] = "<span>(";

                $var[$marker] = true;
                foreach ($var as $key => & $val) {
                    if ($key === $marker)
                        continue;
                    if (!is_int($key)) {
                        $key = '"' . htmlspecialchars($key, ENT_NOQUOTES, Kohana::$charset) . '"';
                    }

                    $output[] = "$space$s$key => " . Debug::_dump($val, $length, $limit, $level + 1);
                }
                unset($var[$marker]);

                $output[] = "$space)</span>";
            } else {
                // Depth too great
                $output[] = "(\n$space$s...\n$space)";
            }

            return '<small>array</small><span>(' . count($var) . ')</span> ' . implode("\n", $output);
        }

        if (is_object($var)) {
            // Copy the object as an array
            $array = (array) $var;

            $output = [];

            // Indentation for this variable
            $space = str_repeat($s = '    ', $level);

            $hash = spl_object_hash($var);

            // Objects that are being dumped
            static $objects = [];

            if (empty($var)) {
                // Do nothing
            } elseif (isset($objects[$hash])) {
                $output[] = "{\n$space$s*RECURSION*\n$space}";
            } elseif ($level < $limit) {
                $output[] = "<code>{";

                $objects[$hash] = true;
                foreach ($array as $key => & $val) {
                    if ($key[0] === "\x00") {
                        // Determine if the access is protected or protected
                        $access = '<small>' . ($key[1] === '*' ? 'protected' : 'private') . '</small>';

                        // Remove the access level from the variable name
                        $key = substr($key, strrpos($key, "\x00") + 1);
                    } else {
                        $access = '<small>public</small>';
                    }

                    $output[] = "$space$s$access $key => " . Debug::_dump($val, $length, $limit, $level + 1);
                }
                unset($objects[$hash]);

                $output[] = "$space}</code>";
            } else {
                // Depth too great
                $output[] = "{\n$space$s...\n$space}";
            }

            return '<small>object</small> <span>' . get_class($var) . '(' . count($array) . ')</span> ' . implode("\n", $output);
        }

        return '<small>' . gettype($var) . '</small> ' . htmlspecialchars(print_r($var, true), ENT_NOQUOTES, Kohana::$charset);
    }

    /**
     * Removes application, system, modpath, or docroot from a filename,
     * replacing them with the plain text equivalents. Useful for debugging
     * when you want to display a shorter path.
     *
     *     // Displays SYSPATH/classes/kohana.php
     *     echo Debug::path(Kohana::find_file('classes', 'kohana'));
     *
     * @param   string  $file   path to debug
     * @return  string
     */
    public static function path($file)
    {
        if (strpos($file, APPPATH) === 0) {
            $file = 'APPPATH' . DIRECTORY_SEPARATOR . substr($file, strlen(APPPATH));
        } elseif (strpos($file, SYSPATH) === 0) {
            $file = 'SYSPATH' . DIRECTORY_SEPARATOR . substr($file, strlen(SYSPATH));
        } elseif (strpos($file, MODPATH) === 0) {
            $file = 'MODPATH' . DIRECTORY_SEPARATOR . substr($file, strlen(MODPATH));
        } elseif (strpos($file, DOCROOT) === 0) {
            $file = 'DOCROOT' . DIRECTORY_SEPARATOR . substr($file, strlen(DOCROOT));
        }

        return $file;
    }

    /**
     * Returns an HTML string, highlighting a specific line of a file, with some
     * number of lines padded above and below.
     *
     *     // Highlights the current line of the current file
     *     echo Debug::source(__FILE__, __LINE__);
     *
     * @param   string  $file           file to open
     * @param   int $line_number line number to highlight
     * @param   int $padding number of padding lines
     * @return  string|false Source of file if readable, false otherwise.
     */
    public static function source($file, $line_number, $padding = 5)
    {
        if (!$file || !is_readable($file)) {
            // Continuing will cause errors
            return false;
        }

        // Open the file and set the line position
        $file = fopen($file, 'r');
        $line = 0;

        // Set the reading range
        $range = ['start' => $line_number - $padding, 'end' => $line_number + $padding];

        // Set the zero-padding amount for line numbers
        $format = '% ' . strlen($range['end']) . 'd';

        $source = '';
        while (($row = fgets($file)) !== false) {
            // Increment the line number
            if (++$line > $range['end'])
                break;

            if ($line >= $range['start']) {
                // Make the row safe for output
                $row = htmlspecialchars($row, ENT_NOQUOTES, Kohana::$charset);

                // Trim whitespace and sanitize the row
                $row = '<span class="number">' . sprintf($format, $line) . '</span> ' . $row;

                if ($line === $line_number) {
                    // Apply highlighting to this row
                    $row = '<span class="line highlight">' . $row . '</span>';
                } else {
                    $row = '<span class="line">' . $row . '</span>';
                }

                // Add to the captured source
                $source .= $row;
            }
        }

        // Close the file
        fclose($file);

        return $source;
    }

    /**
     * Returns an array of HTML strings that represent each step in the backtrace.
     *
     *     // Displays the entire current backtrace
     *     echo implode('<br/>', Debug::trace());
     *
     * @param array $trace
     * @return array
     * @throws ReflectionException
     */
    public static function trace(array $trace = null)
    {
        if ($trace === null) {
            // Start a new trace
            $trace = debug_backtrace();
        }

        // Non-standard function calls
        $statements = ['include', 'include_once', 'require', 'require_once'];

        $output = [];
        foreach ($trace as $step) {
            if (!isset($step['function'])) {
                // Invalid trace step
                continue;
            }

            if (isset($step['file']) && isset($step['line'])) {
                // Include the source of this step
                $source = Debug::source($step['file'], $step['line']);
            }

            if (isset($step['file'])) {
                $file = $step['file'];

                if (isset($step['line'])) {
                    $line = $step['line'];
                }
            }

            // function()
            $function = $step['function'];

            if (in_array($step['function'], $statements)) {
                if (empty($step['args'])) {
                    // No arguments
                    $args = [];
                } else {
                    // Sanitize the file path
                    $args = [$step['args'][0]];
                }
            } elseif (isset($step['args'])) {
                if (!function_exists($step['function']) || strpos($step['function'], '{closure}') !== false) {
                    // Introspection on closures or language constructs in a stack trace is impossible
                    $params = null;
                } else {
                    if (isset($step['class'])) {
                        if (method_exists($step['class'], $step['function'])) {
                            $reflection = new ReflectionMethod($step['class'], $step['function']);
                        } else {
                            $reflection = new ReflectionMethod($step['class'], '__call');
                        }
                    } else {
                        $reflection = new ReflectionFunction($step['function']);
                    }

                    // Get the function parameters
                    $params = $reflection->getParameters();
                }

                $args = [];

                foreach ($step['args'] as $i => $arg) {
                    if (isset($params[$i])) {
                        // Assign the argument by the parameter name
                        $args[$params[$i]->name] = $arg;
                    } else {
                        // Assign the argument by number
                        $args[$i] = $arg;
                    }
                }
            }

            if (isset($step['class'])) {
                // Class->method() or Class::method()
                $function = $step['class'] . $step['type'] . $step['function'];
            }

            $output[] = [
                'function' => $function,
                'args' => $args ?? null,
                'file' => $file ?? null,
                'line' => $line ?? null,
                'source' => $source ?? null,
            ];

            unset($function, $args, $file, $line, $source);
        }

        return $output;
    }

}
