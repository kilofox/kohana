<?php

/**
 * A port of [phputf8](http://phputf8.sourceforge.net/) to a unified set
 * of files. Provides multibyte aware replacement string functions.
 *
 * For UTF-8 support to work correctly, the following requirements must be met:
 *
 * - PCRE needs to be compiled with UTF-8 support (--enable-utf8)
 * - Support for [Unicode properties](https://www.php.net/manual/en/reference.pcre.pattern.modifiers.php)
 *   is highly recommended (--enable-unicode-properties)
 * - The [mbstring extension](https://www.php.net/mbstring) is highly recommended,
 *   but must not be overloading string functions
 *
 * [!!] This file is licensed differently from the rest of Kohana. As a port of
 * [phputf8](http://phputf8.sourceforge.net/), this file is released under the LGPL.
 *
 * @package    Kohana
 * @category   Base
 * @author     Kohana Team
 * @copyright  (c) 2007-2012 Kohana Team
 * @copyright  (c) 2005 Harry Fuecks
 * @license    https://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt
 */
class Kohana_UTF8
{
    /**
     * @var bool Does the server support UTF-8 natively?
     */
    public static $server_utf8 = null;

    /**
     * @var  array  List of called methods that have had their required file included.
     */
    public static $called = [];

    /**
     * Recursively cleans arrays, objects, and strings. Removes ASCII control
     * codes and converts to the requested charset while silently discarding
     * incompatible characters.
     *
     *     UTF8::clean($_GET); // Clean GET data
     *
     * @param   mixed   $var        variable to clean
     * @param   string  $charset    character set, defaults to Kohana::$charset
     * @return  mixed
     * @uses    UTF8::clean
     * @uses    UTF8::strip_ascii_ctrl
     * @uses    UTF8::is_ascii
     */
    public static function clean($var, $charset = null)
    {
        if (!$charset) {
            // Use the application character set
            $charset = Kohana::$charset;
        }

        if (is_array($var) || is_object($var)) {
            foreach ($var as $key => $val) {
                // Recursion!
                $var[UTF8::clean($key)] = UTF8::clean($val);
            }
        } elseif (is_string($var) && $var !== '') {
            // Remove control characters
            $var = UTF8::strip_ascii_ctrl($var);

            if (!UTF8::is_ascii($var)) {
                // Temporarily save the mb_substitute_character() value into a variable
                $mb_substitute_character = mb_substitute_character();

                // Disable substituting illegal characters with the default '?' character
                mb_substitute_character('none');

                // convert encoding, this is expensive, used when $var is not ASCII
                $var = mb_convert_encoding($var, $charset, $charset);

                // Reset mb_substitute_character() value back to the original setting
                mb_substitute_character($mb_substitute_character);
            }
        }

        return $var;
    }

    /**
     * Tests whether a string contains only 7-bit ASCII bytes. This is used to
     * determine when to use native functions or UTF-8 functions.
     *
     *     $ascii = UTF8::is_ascii($str);
     *
     * @param   mixed   $str    string or array of strings to check
     * @return  bool
     */
    public static function is_ascii($str)
    {
        if (is_array($str)) {
            $str = implode($str);
        }

        return !preg_match('/[^\x00-\x7F]/S', $str);
    }

    /**
     * Strips out device control codes in the ASCII range.
     *
     *     $str = UTF8::strip_ascii_ctrl($str);
     *
     * @param   string  $str    string to clean
     * @return  string
     */
    public static function strip_ascii_ctrl($str)
    {
        return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S', '', $str);
    }

    /**
     * Strips out all non-7bit ASCII bytes.
     *
     *     $str = UTF8::strip_non_ascii($str);
     *
     * @param   string  $str    string to clean
     * @return  string
     */
    public static function strip_non_ascii($str)
    {
        return preg_replace('/[^\x00-\x7F]+/S', '', $str);
    }

    /**
     * Replaces special/accented UTF-8 characters by ASCII-7 "equivalents".
     *
     *     $ascii = UTF8::transliterate_to_ascii($utf8);
     *
     * @author  Andreas Gohr <andi@splitbrain.org>
     * @param   string  $str    string to transliterate
     * @param   int $case -1 lowercase only, +1 uppercase only, 0 both cases
     * @return  string
     */
    public static function transliterate_to_ascii($str, $case = 0)
    {
        if (!isset(UTF8::$called[__FUNCTION__])) {
            require Kohana::find_file('utf8', __FUNCTION__);

            // Function has been called
            UTF8::$called[__FUNCTION__] = true;
        }

        return _transliterate_to_ascii($str, $case);
    }

    /**
     * Returns the length of the given string. This is a UTF8-aware version
     * of [strlen](https://www.php.net/strlen).
     *
     *     $length = UTF8::strlen($str);
     *
     * @param   string  $str    string being measured for length
     * @return  int
     * @uses    UTF8::$server_utf8
     * @uses    Kohana::$charset
     */
    public static function strlen($str)
    {
        if (UTF8::$server_utf8)
            return mb_strlen($str, Kohana::$charset);

        if (!isset(UTF8::$called[__FUNCTION__])) {
            require Kohana::find_file('utf8', __FUNCTION__);

            // Function has been called
            UTF8::$called[__FUNCTION__] = true;
        }

        return _strlen($str);
    }

    /**
     * Finds position of first occurrence of a UTF-8 string. This is a
     * UTF8-aware version of [strpos](https://www.php.net/strpos).
     *
     *     $position = UTF8::strpos($str, $search);
     *
     * @author  Harry Fuecks <hfuecks@gmail.com>
     * @param   string  $str    haystack
     * @param   string  $search needle
     * @param   int $offset offset from which character in haystack to start searching
     * @return  int|false Position of needle if found, false otherwise.
     * @uses    UTF8::$server_utf8
     * @uses    Kohana::$charset
     */
    public static function strpos($str, $search, $offset = 0)
    {
        if (UTF8::$server_utf8)
            return mb_strpos($str, $search, $offset, Kohana::$charset);

        if (!isset(UTF8::$called[__FUNCTION__])) {
            require Kohana::find_file('utf8', __FUNCTION__);

            // Function has been called
            UTF8::$called[__FUNCTION__] = true;
        }

        return _strpos($str, $search, $offset);
    }

    /**
     * Finds position of last occurrence of a char in a UTF-8 string. This is
     * a UTF8-aware version of [strrpos](https://www.php.net/strrpos).
     *
     *     $position = UTF8::strrpos($str, $search);
     *
     * @author  Harry Fuecks <hfuecks@gmail.com>
     * @param   string  $str    haystack
     * @param   string  $search needle
     * @param   int $offset offset from which character in haystack to start searching
     * @return  int|false Position of needle if found, false otherwise.
     * @uses    UTF8::$server_utf8
     */
    public static function strrpos($str, $search, $offset = 0)
    {
        if (UTF8::$server_utf8)
            return mb_strrpos($str, $search, $offset, Kohana::$charset);

        if (!isset(UTF8::$called[__FUNCTION__])) {
            require Kohana::find_file('utf8', __FUNCTION__);

            // Function has been called
            UTF8::$called[__FUNCTION__] = true;
        }

        return _strrpos($str, $search, $offset);
    }

    /**
     * Returns part of a UTF-8 string. This is a UTF8-aware version
     * of [substr](https://www.php.net/substr).
     *
     *     $sub = UTF8::substr($str, $offset);
     *
     * @author  Chris Smith <chris@jalakai.co.uk>
     * @param   string  $str    input string
     * @param   int $offset offset
     * @param   int $length length limit
     * @return  string
     * @uses    UTF8::$server_utf8
     * @uses    Kohana::$charset
     */
    public static function substr($str, $offset, $length = null)
    {
        if (UTF8::$server_utf8)
            return ($length === null) ? mb_substr($str, $offset, mb_strlen($str), Kohana::$charset) : mb_substr($str, $offset, $length, Kohana::$charset);

        if (!isset(UTF8::$called[__FUNCTION__])) {
            require Kohana::find_file('utf8', __FUNCTION__);

            // Function has been called
            UTF8::$called[__FUNCTION__] = true;
        }

        return _substr($str, $offset, $length);
    }

    /**
     * Replaces text within a portion of a UTF-8 string. This is a UTF8-aware
     * version of [substr_replace](https://www.php.net/substr_replace).
     *
     *     $str = UTF8::substr_replace($str, $replacement, $offset);
     *
     * @author  Harry Fuecks <hfuecks@gmail.com>
     * @param   string  $str            input string
     * @param   string  $replacement    replacement string
     * @param   int $offset offset
     * @return  string
     */
    public static function substr_replace($str, $replacement, $offset, $length = null)
    {
        if (!isset(UTF8::$called[__FUNCTION__])) {
            require Kohana::find_file('utf8', __FUNCTION__);

            // Function has been called
            UTF8::$called[__FUNCTION__] = true;
        }

        return _substr_replace($str, $replacement, $offset, $length);
    }

    /**
     * Makes a UTF-8 string lowercase. This is a UTF8-aware version
     * of [strtolower](https://www.php.net/strtolower).
     *
     *     $str = UTF8::strtolower($str);
     *
     * @author  Andreas Gohr <andi@splitbrain.org>
     * @param   string  $str mixed case string
     * @return  string
     * @uses    UTF8::$server_utf8
     * @uses    Kohana::$charset
     */
    public static function strtolower($str)
    {
        if (UTF8::$server_utf8)
            return mb_strtolower($str, Kohana::$charset);

        if (!isset(UTF8::$called[__FUNCTION__])) {
            require Kohana::find_file('utf8', __FUNCTION__);

            // Function has been called
            UTF8::$called[__FUNCTION__] = true;
        }

        return _strtolower($str);
    }

    /**
     * Makes a UTF-8 string uppercase. This is a UTF8-aware version
     * of [strtoupper](https://www.php.net/strtoupper).
     *
     * @author  Andreas Gohr <andi@splitbrain.org>
     * @param   string  $str mixed case string
     * @return  string
     * @uses    UTF8::$server_utf8
     * @uses    Kohana::$charset
     */
    public static function strtoupper($str)
    {
        if (UTF8::$server_utf8)
            return mb_strtoupper($str, Kohana::$charset);

        if (!isset(UTF8::$called[__FUNCTION__])) {
            require Kohana::find_file('utf8', __FUNCTION__);

            // Function has been called
            UTF8::$called[__FUNCTION__] = true;
        }

        return _strtoupper($str);
    }

    /**
     * Makes a UTF-8 string's first character uppercase. This is a UTF8-aware
     * version of [ucfirst](https://www.php.net/ucfirst).
     *
     *     $str = UTF8::ucfirst($str);
     *
     * @author  Harry Fuecks <hfuecks@gmail.com>
     * @param   string  $str mixed case string
     * @return  string
     */
    public static function ucfirst($str)
    {
        if (!isset(UTF8::$called[__FUNCTION__])) {
            require Kohana::find_file('utf8', __FUNCTION__);

            // Function has been called
            UTF8::$called[__FUNCTION__] = true;
        }

        return _ucfirst($str);
    }

    /**
     * Makes the first character of every word in a UTF-8 string uppercase.
     * This is a UTF8-aware version of [ucwords](https://www.php.net/ucwords).
     *
     *     $str = UTF8::ucwords($str);
     *
     * @author  Harry Fuecks <hfuecks@gmail.com>
     * @param   string  $str mixed case string
     * @return  string
     */
    public static function ucwords($str)
    {
        if (!isset(UTF8::$called[__FUNCTION__])) {
            require Kohana::find_file('utf8', __FUNCTION__);

            // Function has been called
            UTF8::$called[__FUNCTION__] = true;
        }

        return _ucwords($str);
    }

    /**
     * Case-insensitive UTF-8 string comparison. This is a UTF8-aware version
     * of [strcasecmp](https://www.php.net/strcasecmp).
     *
     *     $compare = UTF8::strcasecmp($str1, $str2);
     *
     * @author  Harry Fuecks <hfuecks@gmail.com>
     * @param   string  $str1   string to compare
     * @param   string  $str2   string to compare
     * @return  int Less than 0 if str1 is less than str2, greater than 0 if str1 is greater than str2, or 0 if they are equal.
     */
    public static function strcasecmp($str1, $str2)
    {
        if (!isset(UTF8::$called[__FUNCTION__])) {
            require Kohana::find_file('utf8', __FUNCTION__);

            // Function has been called
            UTF8::$called[__FUNCTION__] = true;
        }

        return _strcasecmp($str1, $str2);
    }

    /**
     * Returns a string or an array with all occurrences of search in subject
     * (ignoring case) and replaced with the given replace value. This is a
     * UTF8-aware version of [str_ireplace](https://www.php.net/str_ireplace).
     *
     * [!!] This function is very slow compared to the native version. Avoid
     * using it when possible.
     *
     * @author  Harry Fuecks <hfuecks@gmail.com
     * @param   string|array    $search     text to replace
     * @param   string|array    $replace    replacement text
     * @param   string|array    $str        subject text
     * @param   int $count number of matched and replaced needles will be returned via this parameter which is passed by reference
     * @return  string|array Replaced value, same type as input.
     */
    public static function str_ireplace($search, $replace, $str, & $count = null)
    {
        if (!isset(UTF8::$called[__FUNCTION__])) {
            require Kohana::find_file('utf8', __FUNCTION__);

            // Function has been called
            UTF8::$called[__FUNCTION__] = true;
        }

        return _str_ireplace($search, $replace, $str, $count);
    }

    /**
     * Case-insensitive UTF-8 version of strstr. Returns all of input string
     * from the first occurrence of needle to the end. This is a UTF8-aware
     * version of [stristr](https://www.php.net/stristr).
     *
     *     $found = UTF8::stristr($str, $search);
     *
     * @author  Harry Fuecks <hfuecks@gmail.com>
     * @param   string  $str    input string
     * @param   string  $search needle
     * @return  string|false Matched substring if found, false otherwise.
     */
    public static function stristr($str, $search)
    {
        if (!isset(UTF8::$called[__FUNCTION__])) {
            require Kohana::find_file('utf8', __FUNCTION__);

            // Function has been called
            UTF8::$called[__FUNCTION__] = true;
        }

        return _stristr($str, $search);
    }

    /**
     * Finds the length of the initial segment matching mask. This is a
     * UTF8-aware version of [strspn](https://www.php.net/strspn).
     *
     *     $found = UTF8::strspn($str, $mask);
     *
     * @author  Harry Fuecks <hfuecks@gmail.com>
     * @param   string  $str    input string
     * @param   string  $mask   mask for search
     * @param   int $offset start position of the string to examine
     * @param   int $length length of the string to examine
     * @return  int length of the initial segment that contains characters in the mask
     */
    public static function strspn($str, $mask, $offset = null, $length = null)
    {
        if (!isset(UTF8::$called[__FUNCTION__])) {
            require Kohana::find_file('utf8', __FUNCTION__);

            // Function has been called
            UTF8::$called[__FUNCTION__] = true;
        }

        return _strspn($str, $mask, $offset, $length);
    }

    /**
     * Finds the length of the initial segment not matching mask. This is a
     * UTF8-aware version of [strcspn](https://www.php.net/strcspn).
     *
     *     $found = UTF8::strcspn($str, $mask);
     *
     * @author  Harry Fuecks <hfuecks@gmail.com>
     * @param   string  $str    input string
     * @param   string  $mask   mask for search
     * @param   int $offset start position of the string to examine
     * @param   int $length length of the string to examine
     * @return  int length of the initial segment that contains characters not in the mask
     */
    public static function strcspn($str, $mask, $offset = null, $length = null)
    {
        if (!isset(UTF8::$called[__FUNCTION__])) {
            require Kohana::find_file('utf8', __FUNCTION__);

            // Function has been called
            UTF8::$called[__FUNCTION__] = true;
        }

        return _strcspn($str, $mask, $offset, $length);
    }

    /**
     * Pads a UTF-8 string to a certain length with another string. This is a
     * UTF8-aware version of [str_pad](https://www.php.net/str_pad).
     *
     *     $str = UTF8::str_pad($str, $length);
     *
     * @param string $str input string
     * @param int $final_str_length desired string length after padding
     * @param string $pad_str string to use as padding
     * @param string $pad_type padding type: STR_PAD_RIGHT, STR_PAD_LEFT, or STR_PAD_BOTH
     * @return  string
     * @throws UTF8_Exception
     * @author  Harry Fuecks <hfuecks@gmail.com>
     */
    public static function str_pad($str, $final_str_length, $pad_str = ' ', $pad_type = STR_PAD_RIGHT)
    {
        if (!isset(UTF8::$called[__FUNCTION__])) {
            require Kohana::find_file('utf8', __FUNCTION__);

            // Function has been called
            UTF8::$called[__FUNCTION__] = true;
        }

        return _str_pad($str, $final_str_length, $pad_str, $pad_type);
    }

    /**
     * Converts a UTF-8 string to an array. This is a UTF8-aware version of
     * [str_split](https://www.php.net/str_split).
     *
     *     $array = UTF8::str_split($str);
     *
     * @author  Harry Fuecks <hfuecks@gmail.com>
     * @param   string  $str            input string
     * @param   int $split_length maximum length of each chunk
     * @return  array
     */
    public static function str_split($str, $split_length = 1)
    {
        if (!isset(UTF8::$called[__FUNCTION__])) {
            require Kohana::find_file('utf8', __FUNCTION__);

            // Function has been called
            UTF8::$called[__FUNCTION__] = true;
        }

        return _str_split($str, $split_length);
    }

    /**
     * Reverses a UTF-8 string. This is a UTF8-aware version of [strrev](https://www.php.net/strrev).
     *
     *     $str = UTF8::strrev($str);
     *
     * @author  Harry Fuecks <hfuecks@gmail.com>
     * @param   string  $str string to be reversed
     * @return  string
     */
    public static function strrev($str)
    {
        if (!isset(UTF8::$called[__FUNCTION__])) {
            require Kohana::find_file('utf8', __FUNCTION__);

            // Function has been called
            UTF8::$called[__FUNCTION__] = true;
        }

        return _strrev($str);
    }

    /**
     * Strips whitespace (or other UTF-8 characters) from the beginning and
     * end of a string. This is a UTF8-aware version of [trim](https://www.php.net/trim).
     *
     *     $str = UTF8::trim($str);
     *
     * @author  Andreas Gohr <andi@splitbrain.org>
     * @param   string  $str        input string
     * @param   string  $charlist   string of characters to remove
     * @return  string
     */
    public static function trim($str, $charlist = null)
    {
        if (!isset(UTF8::$called[__FUNCTION__])) {
            require Kohana::find_file('utf8', __FUNCTION__);

            // Function has been called
            UTF8::$called[__FUNCTION__] = true;
        }

        return _trim($str, $charlist);
    }

    /**
     * Strips whitespace (or other UTF-8 characters) from the beginning of
     * a string. This is a UTF8-aware version of [ltrim](https://www.php.net/ltrim).
     *
     *     $str = UTF8::ltrim($str);
     *
     * @author  Andreas Gohr <andi@splitbrain.org>
     * @param   string  $str        input string
     * @param   string  $charlist   string of characters to remove
     * @return  string
     */
    public static function ltrim($str, $charlist = null)
    {
        if (!isset(UTF8::$called[__FUNCTION__])) {
            require Kohana::find_file('utf8', __FUNCTION__);

            // Function has been called
            UTF8::$called[__FUNCTION__] = true;
        }

        return _ltrim($str, $charlist);
    }

    /**
     * Strips whitespace (or other UTF-8 characters) from the end of a string.
     * This is a UTF8-aware version of [rtrim](https://www.php.net/rtrim).
     *
     *     $str = UTF8::rtrim($str);
     *
     * @author  Andreas Gohr <andi@splitbrain.org>
     * @param   string  $str        input string
     * @param   string  $charlist   string of characters to remove
     * @return  string
     */
    public static function rtrim($str, $charlist = null)
    {
        if (!isset(UTF8::$called[__FUNCTION__])) {
            require Kohana::find_file('utf8', __FUNCTION__);

            // Function has been called
            UTF8::$called[__FUNCTION__] = true;
        }

        return _rtrim($str, $charlist);
    }

    /**
     * Returns the Unicode ordinal for a character. This is a UTF8-aware
     * version of [ord](https://www.php.net/ord).
     *
     *     $digit = UTF8::ord($character);
     *
     * @param string $chr UTF-8 encoded character
     * @return int
     * @throws UTF8_Exception
     * @author  Harry Fuecks <hfuecks@gmail.com>
     */
    public static function ord($chr)
    {
        if (!isset(UTF8::$called[__FUNCTION__])) {
            require Kohana::find_file('utf8', __FUNCTION__);

            // Function has been called
            UTF8::$called[__FUNCTION__] = true;
        }

        return _ord($chr);
    }

    /**
     * Takes a UTF-8 string and returns an array of ints representing the Unicode characters.
     * Astral planes are supported i.e. the ints in the output can be > 0xFFFF.
     * Occurrences of the BOM are ignored. Surrogates are not allowed.
     *
     *     $array = UTF8::to_unicode($str);
     *
     * The Original Code is Mozilla Communicator client code.
     * The Initial Developer of the Original Code is Netscape Communications Corporation.
     * Portions created by the Initial Developer are Copyright (C) 1998 the Initial Developer.
     * Ported to PHP by Henri Sivonen <hsivonen@iki.fi>, see <http://hsivonen.iki.fi/php-utf8/>
     * Slight modifications to fit with phputf8 library by Harry Fuecks <hfuecks@gmail.com>
     *
     * @param string $str UTF-8 encoded string
     * @return array|false Unicode code points if succeeded, or false if the string is invalid.
     * @throws UTF8_Exception
     */
    public static function to_unicode($str)
    {
        if (!isset(UTF8::$called[__FUNCTION__])) {
            require Kohana::find_file('utf8', __FUNCTION__);

            // Function has been called
            UTF8::$called[__FUNCTION__] = true;
        }

        return _to_unicode($str);
    }

    /**
     * Takes an array of ints representing the Unicode characters and returns a UTF-8 string.
     * Astral planes are supported i.e. the ints in the input can be > 0xFFFF.
     * Occurrences of the BOM are ignored. Surrogates are not allowed.
     *
     *     $str = UTF8::to_unicode($array);
     *
     * The Original Code is Mozilla Communicator client code.
     * The Initial Developer of the Original Code is Netscape Communications Corporation.
     * Portions created by the Initial Developer are Copyright (C) 1998 the Initial Developer.
     * Ported to PHP by Henri Sivonen <hsivonen@iki.fi>, see http://hsivonen.iki.fi/php-utf8/
     * Slight modifications to fit with phputf8 library by Harry Fuecks <hfuecks@gmail.com>.
     *
     * @param array $arr Unicode code points representing a string
     * @return string|false UTF-8 encoded string on success, false if invalid code point encountered.
     * @throws UTF8_Exception
     */
    public static function from_unicode($arr)
    {
        if (!isset(UTF8::$called[__FUNCTION__])) {
            require Kohana::find_file('utf8', __FUNCTION__);

            // Function has been called
            UTF8::$called[__FUNCTION__] = true;
        }

        return _from_unicode($arr);
    }

}

if (Kohana_UTF8::$server_utf8 === null) {
    // Determine if this server supports UTF-8 natively
    Kohana_UTF8::$server_utf8 = extension_loaded('mbstring');
}
