<?php

/**
 * Array helper.
 *
 * @package    Kohana
 * @category   Helpers
 * @author     Kohana Team
 * @copyright  (c) 2007-2012 Kohana Team
 * @license    https://kohana.top/license
 */
class Kohana_Arr
{
    /**
     * @var  string  default delimiter for path()
     */
    public static $delimiter = '.';

    /**
     * Tests if an array is associative or not.
     *
     *     // Returns true
     *     Arr::is_assoc(['username' => 'john.doe']);
     *
     *     // Returns false
     *     Arr::is_assoc('foo', 'bar');
     *
     * @param   array   $array  array to check
     * @return  bool
     */
    public static function is_assoc(array $array)
    {
        // Keys of the array
        $keys = array_keys($array);

        // If the array keys of the keys match the keys, then the array must
        // not be associative (e.g. the keys array looked like {0:0, 1:1...}).
        return array_keys($keys) !== $keys;
    }

    /**
     * Test if a value is an array with an additional check for array-like objects.
     *
     *     // Returns true
     *     Arr::is_array([]);
     *     Arr::is_array(new ArrayObject);
     *
     *     // Returns false
     *     Arr::is_array(false);
     *     Arr::is_array('not an array!');
     *     Arr::is_array(Database::instance());
     *
     * @param   mixed   $value  value to check
     * @return  bool
     */
    public static function is_array($value)
    {
        if (is_array($value)) {
            // Definitely an array
            return true;
        } else {
            // Possibly a Traversable object, functionally the same as an array
            return is_object($value) && $value instanceof Traversable;
        }
    }

    /**
     * Gets a value from an array using a dot separated path.
     *
     *     // Get the value of $array['foo']['bar']
     *     $value = Arr::path($array, 'foo.bar');
     *
     * Using a wildcard "*" will search intermediate arrays and return an array.
     *
     *     // Get the values of "color" in theme
     *     $colors = Arr::path($array, 'theme.*.color');
     *
     *     // Using an array of keys
     *     $colors = Arr::path($array, ['theme', '*', 'color']);
     *
     * @param   array   $array      array to search
     * @param   mixed   $path       key path string (delimiter separated) or array of keys
     * @param   mixed   $default    default value if the path is not set
     * @param   string  $delimiter  key path delimiter
     * @return  mixed
     */
    public static function path($array, $path, $default = null, $delimiter = null)
    {
        if (is_array($path)) {
            // The path has already been separated into keys
            $keys = $path;
        } else {
            if (array_key_exists($path, $array)) {
                // No need to do extra processing
                return $array[$path];
            }

            if ($delimiter === null) {
                // Use the default delimiter
                $delimiter = Arr::$delimiter;
            }

            // Remove starting delimiters and spaces
            $path = ltrim($path, "$delimiter ");

            // Remove ending delimiters, spaces, and wildcards
            $path = rtrim($path, "$delimiter *");

            // Split the keys by delimiter
            $keys = explode($delimiter, $path);
        }

        do {
            $key = array_shift($keys);

            if (ctype_digit($key)) {
                // Make the key an integer
                $key = (int) $key;
            }

            if (isset($array[$key])) {
                if ($keys) {
                    if (Arr::is_array($array[$key])) {
                        // Dig down into the next part of the path
                        $array = $array[$key];
                    } else {
                        // Unable to dig deeper
                        break;
                    }
                } else {
                    // Found the path requested
                    return $array[$key];
                }
            } elseif ($key === '*') {
                // Handle wildcards

                $values = [];
                foreach ($array as $arr) {
                    if (!is_array($arr)) {
                        if (Arr::is_array($arr)) {
                            $arr = iterator_to_array($arr);
                        } else {
                            continue;
                        }
                    }
                    if ($value = Arr::path($arr, $keys, $default, $delimiter)) {
                        $values[] = $value;
                    }
                }

                if ($values) {
                    // Found the values requested
                    return $values;
                } else {
                    // Unable to dig deeper
                    break;
                }
            } else {
                // Unable to dig deeper
                break;
            }
        } while ($keys);

        // Unable to find the value requested
        return $default;
    }

    /**
     * Set a value on an array by path.
     *
     * @see Arr::path()
     * @param array   $array     Array to update
     * @param string  $path      Path
     * @param mixed   $value     Value to set
     * @param string  $delimiter Path delimiter
     */
    public static function set_path(& $array, $path, $value, $delimiter = null)
    {
        if (!$delimiter) {
            // Use the default delimiter
            $delimiter = Arr::$delimiter;
        }

        // The path has already been separated into keys
        $keys = $path;
        if (!is_array($path)) {
            // Split the keys by delimiter
            $keys = explode($delimiter, $path);
        }

        // Set current $array to inner-most array path
        while (count($keys) > 1) {
            $key = array_shift($keys);

            if (ctype_digit($key)) {
                // Make the key an integer
                $key = (int) $key;
            }

            if (!isset($array[$key])) {
                $array[$key] = [];
            }

            $array = & $array[$key];
        }

        // Set key on inner-most array
        $array[array_shift($keys)] = $value;
    }

    /**
     * Fill an array with a range of numbers.
     *
     *     // Fill an array with values 5, 10, 15, 20
     *     $values = Arr::range(5, 20);
     *
     * @param   int $step   stepping
     * @param   int $max    ending number
     * @return  array
     */
    public static function range($step = 10, $max = 100)
    {
        if ($step < 1)
            return [];

        $array = [];
        for ($i = $step; $i <= $max; $i += $step) {
            $array[$i] = $i;
        }

        return $array;
    }

    /**
     * Retrieve a single key from an array. If the key does not exist in the
     * array, the default value will be returned instead.
     *
     *     // Get the value "username" from $_POST, if it exists
     *     $username = Arr::get($_POST, 'username');
     *
     *     // Get the value "sorting" from $_GET, if it exists
     *     $sorting = Arr::get($_GET, 'sorting');
     *
     * @param   array   $array      array to extract from
     * @param   string  $key        key name
     * @param   mixed   $default    default value
     * @return  mixed
     */
    public static function get($array, $key, $default = null)
    {
        return isset($array[$key]) ? $array[$key] : $default;
    }

    /**
     * Retrieves multiple paths from an array. If the path does not exist in the
     * array, the default value will be added instead.
     *
     *     // Get the values "username", "password" from $_POST
     *     $auth = Arr::extract($_POST, ['username', 'password']);
     *
     *     // Get the value "level1.level2a" from $data
     *     $data = ['level1' => ['level2a' => 'value 1', 'level2b' => 'value 2']];
     *     Arr::extract($data, ['level1.level2a', 'password']);
     *
     * @param   array  $array    array to extract paths from
     * @param   array  $paths    list of path
     * @param   mixed  $default  default value
     * @return  array
     */
    public static function extract($array, array $paths, $default = null)
    {
        $found = [];
        foreach ($paths as $path) {
            Arr::set_path($found, $path, Arr::path($array, $path, $default));
        }

        return $found;
    }

    /**
     * Retrieves multiple single-key values from a list of arrays.
     *
     *     // Get all the "id" values from a result
     *     $ids = Arr::pluck($result, 'id');
     *
     * [!!] A list of arrays is an array that contains arrays, e.g., [array $a, array $b, array $c, ...]
     *
     * @param   array   $array  list of arrays to check
     * @param   string  $key    key to pluck
     * @return  array
     */
    public static function pluck($array, $key)
    {
        $values = [];

        foreach ($array as $row) {
            if (isset($row[$key])) {
                // Found a value in this row
                $values[] = $row[$key];
            }
        }

        return $values;
    }

    /**
     * Adds a value to the beginning of an associative array.
     *
     *     // Add an empty value to the start of a select list
     *     Arr::unshift($array, 'none', 'Select a value');
     *
     * @param   array   $array  array to modify
     * @param   string  $key    array key name
     * @param   mixed   $val    array value
     * @return  array
     */
    public static function unshift(array & $array, $key, $val)
    {
        $array = array_reverse($array, true);
        $array[$key] = $val;
        $array = array_reverse($array, true);

        return $array;
    }

    /**
     * Recursive version of [array_map](https://www.php.net/array_map), applies one or more
     * callbacks to all elements in an array, including sub-arrays.
     *
     *     // Apply "strip_tags" to every element in the array
     *     $array = Arr::map('strip_tags', $array);
     *
     *     // Apply $this->filter to every element in the array
     *     $array = Arr::map([[$this, 'filter']], $array);
     *
     *     // Apply strip_tags and $this->filter to every element
     *     $array = Arr::map(['strip_tags', [$this,'filter']], $array);
     *
     * [!!] Because you can pass an array of callbacks, if you wish to use an array-form callback
     * you must nest it in an additional array as above. Calling Arr::map([$this,'filter'], $array)
     * will cause an error.
     * [!!] Unlike `array_map`, this method requires a callback and will only map
     * a single array.
     *
     * @param   mixed   $callbacks  array of callbacks to apply to every element in the array
     * @param   array   $array      array to map
     * @param   array   $keys       array of keys to apply to
     * @return  array
     */
    public static function map($callbacks, $array, $keys = null)
    {
        foreach ($array as $key => $val) {
            if (is_array($val)) {
                $array[$key] = Arr::map($callbacks, $val, $keys);
            } elseif (!is_array($keys) || in_array($key, $keys)) {
                if (is_array($callbacks)) {
                    foreach ($callbacks as $cb) {
                        $array[$key] = call_user_func($cb, $array[$key]);
                    }
                } else {
                    $array[$key] = call_user_func($callbacks, $val);
                }
            }
        }

        return $array;
    }

    /**
     * Recursively merge two or more arrays. Values in an associative array
     * overwrite previous values with the same key. Values in an indexed array
     * are appended, but only when they do not already exist in the result.
     *
     * Note that this does not work the same as [array_merge_recursive](https://www.php.net/array_merge_recursive)!
     *
     *     $john = ['name' => 'john', 'children' => ['fred', 'paul', 'sally', 'jane']];
     *     $mary = ['name' => 'mary', 'children' => ['jane']];
     *
     *     // John and Mary are married, merge them together
     *     $john = Arr::merge($john, $mary);
     *
     *     // The output of $john will now be:
     *     ['name' => 'mary', 'children' => ['fred', 'paul', 'sally', 'jane']]
     *
     * @param   array  $array1      initial array
     * @param   array  ...$arrays   array to merge
     * @return  array
     */
    public static function merge($array1, ...$arrays)
    {
        foreach ($arrays as $array2) {
            if (Arr::is_assoc($array2)) {
                foreach ($array2 as $key => $value) {
                    if (is_array($value) && isset($array1[$key]) && is_array($array1[$key])) {
                        $array1[$key] = Arr::merge($array1[$key], $value);
                    } else {
                        $array1[$key] = $value;
                    }
                }
            } else {
                foreach ($array2 as $value) {
                    if (!in_array($value, $array1, true)) {
                        $array1[] = $value;
                    }
                }
            }
        }

        return $array1;
    }

    /**
     * Overwrites an array with values from input arrays.
     * Keys that do not exist in the first array will not be added!
     *
     *     $a1 = ['name' => 'john', 'mood' => 'happy', 'food' => 'bacon'];
     *     $a2 = ['name' => 'jack', 'food' => 'tacos', 'drink' => 'beer'];
     *
     *     // Overwrite the values of $a1 with $a2
     *     $array = Arr::overwrite($a1, $a2);
     *
     *     // The output of $array will now be:
     *     ['name' => 'jack', 'mood' => 'happy', 'food' => 'tacos']
     *
     * @param   array   $array1 master array
     * @param   array   ...$arrays input arrays that will overwrite existing values
     * @return  array
     */
    public static function overwrite($array1, ...$arrays)
    {
        foreach ($arrays as $array2) {
            foreach (array_intersect_key($array2, $array1) as $key => $value) {
                $array1[$key] = $value;
            }
        }

        return $array1;
    }

    /**
     * Creates a callable function and parameter list from a string representation.
     * Note that this function does not validate the callback string.
     *
     *     // Get the callback function and parameters
     *     list($func, $params) = Arr::callback('Foo::bar(apple,orange)');
     *
     *     // Get the result of the callback
     *     $result = call_user_func_array($func, $params);
     *
     * @param   string  $str    callback string
     * @return  array   function, params
     */
    public static function callback($str)
    {
        // Overloaded as parts are found
        $params = null;

        // command[param,param]
        if (preg_match('/^([^\(]*+)\((.*)\)$/', $str, $match)) {
            // command
            $command = $match[1];

            if ($match[2] !== '') {
                // param,param
                $params = preg_split('/(?<!\\\\),/', $match[2]);
                $params = str_replace('\,', ',', $params);
            }
        } else {
            // command
            $command = $str;
        }

        if (strpos($command, '::') !== false) {
            // Create a static method callable command
            $command = explode('::', $command, 2);
        }

        return [$command, $params];
    }

    /**
     * Convert a multi-dimensional array into a single-dimensional array.
     *
     *     $array = ['set' => ['one' => 'something'], 'two' => 'other'];
     *
     *     // Flatten the array
     *     $array = Arr::flatten($array);
     *
     *     // The array will now be
     *     ['one' => 'something', 'two' => 'other'];
     *
     * [!!] The keys of array values will be discarded.
     *
     * @param   array   $array  array to flatten
     * @return  array
     * @since   3.0.6
     */
    public static function flatten($array)
    {
        $is_assoc = Arr::is_assoc($array);

        $flat = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $flat = array_merge($flat, Arr::flatten($value));
            } else {
                if ($is_assoc) {
                    $flat[$key] = $value;
                } else {
                    $flat[] = $value;
                }
            }
        }
        return $flat;
    }

}
