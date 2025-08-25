<?php

/**
 * Validation rules.
 *
 * @package    Kohana
 * @category   Security
 * @author     Kohana Team
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    https://kohana.top/license
 */
class Kohana_Valid
{
    /**
     * Checks if a field is not empty.
     *
     * @return bool
     */
    public static function not_empty($value): bool
    {
        if ($value instanceof ArrayObject) {
            // Get the array from the ArrayObject
            $value = $value->getArrayCopy();
        }

        // Value cannot be null, false, '', or an empty array
        return !in_array($value, [null, false, '', []], true);
    }

    /**
     * Checks a field against a regular expression.
     *
     * @param mixed $value Value
     * @param string $expression Regular expression to match (including delimiters)
     * @return  bool
     */
    public static function regex($value, string $expression): bool
    {
        return (bool) preg_match($expression, (string) $value);
    }

    /**
     * Checks that a field is long enough.
     *
     * @param string $value Value
     * @param int $length Minimum length required
     * @return  bool
     */
    public static function min_length(string $value, int $length): bool
    {
        return UTF8::strlen($value) >= $length;
    }

    /**
     * Checks that a field is short enough.
     *
     * @param string $value Value
     * @param int $length Maximum length required
     * @return  bool
     */
    public static function max_length(string $value, int $length): bool
    {
        return UTF8::strlen($value) <= $length;
    }

    /**
     * Checks that a field is exactly the right length.
     *
     * @param string $value Value
     * @param   int|array $length exact length required, or array of valid lengths
     * @return  bool
     */
    public static function exact_length(string $value, $length): bool
    {
        if (is_array($length)) {
            return in_array(UTF8::strlen($value), $length, true);
        }

        return UTF8::strlen($value) === $length;
    }

    /**
     * Checks that a field is exactly the value required.
     *
     * @param mixed $value Value
     * @param mixed $required Required value
     * @return  bool
     */
    public static function equals($value, $required): bool
    {
        return $value === $required;
    }

    /**
     * Check an email address for correct format.
     *
     * @link  http://www.iamcal.com/publish/articles/php/parsing_email/
     * @link  http://www.w3.org/Protocols/rfc822/
     *
     * @param string $email Email address
     * @param bool $strict Strict RFC compatibility
     * @return  bool
     */
    public static function email(string $email, bool $strict = false): bool
    {
        if (UTF8::strlen($email) > 254) {
            return false;
        }

        if ($strict === true) {
            $qtext = '[^\\x0d\\x22\\x5c\\x80-\\xff]';
            $dtext = '[^\\x0d\\x5b-\\x5d\\x80-\\xff]';
            $atom = '[^\\x00-\\x20\\x22\\x28\\x29\\x2c\\x2e\\x3a-\\x3c\\x3e\\x40\\x5b-\\x5d\\x7f-\\xff]+';
            $pair = '\\x5c[\\x00-\\x7f]';

            $domain_literal = "\\x5b($dtext|$pair)*\\x5d";
            $quoted_string = "\\x22($qtext|$pair)*\\x22";
            $sub_domain = "($atom|$domain_literal)";
            $word = "($atom|$quoted_string)";
            $domain = "$sub_domain(\\x2e$sub_domain)*";
            $local_part = "$word(\\x2e$word)*";

            $expression = "/^$local_part\\x40$domain$/D";
        } else {
            $expression = '/^[-_a-z0-9\'+*$^&%=~!?{}]++(?:\.[-_a-z0-9\'+*$^&%=~!?{}]+)*+@(?:(?![-.])[-a-z0-9.]+(?<![-.])\.[a-z]{2,6}|\d{1,3}(?:\.\d{1,3}){3})$/iD';
        }

        return (bool) preg_match($expression, $email);
    }

    /**
     * Validate the domain of an email address by checking if the domain has a
     * valid MX record.
     *
     * @link  https://www.php.net/checkdnsrr  not added to Windows until PHP 5.3.0
     *
     * @param string $email Email address
     * @return  bool
     */
    public static function email_domain(string $email): bool
    {
        if (!Valid::not_empty($email))
            return false; // Empty fields cause issues with checkdnsrr()

        // Check if the email domain has a valid MX record
        return checkdnsrr(preg_replace('/^[^@]++@/', '', $email));
    }

    /**
     * Validate a URL.
     *
     * @param string $url URL
     * @return  bool
     */
    public static function url(string $url): bool
    {
        // Based on https://datatracker.ietf.org/doc/html/rfc1738#section-5
        if (!preg_match(
                '~^

			# scheme
			[-a-z0-9+.]++://

			# username:password (optional)
			(?:
				    [-a-z0-9$_.+!*\'(),;?&=%]++   # username
				(?::[-a-z0-9$_.+!*\'(),;?&=%]++)? # password (optional)
				@
			)?

			(?:
				# ip address
				\d{1,3}+(?:\.\d{1,3}+){3}+

				| # or

				# hostname (captured)
				(
					     (?!-)[-a-z0-9]{1,63}+(?<!-)
					(?:\.(?!-)[-a-z0-9]{1,63}+(?<!-)){0,126}+
				)
			)

			# port (optional)
			(?::\d{1,5}+)?

			# path (optional)
			(?:/.*)?

			$~iDx', $url, $matches))
            return false;

        // We matched an IP address
        if (!isset($matches[1]))
            return true;

        // Check maximum length of the whole hostname
        // https://en.wikipedia.org/wiki/Domain_name#Domain_name_syntax
        if (strlen($matches[1]) > 253)
            return false;

        // An extra check for the top level domain
        // It must start with a letter
        $tld = ltrim(substr($matches[1], (int) strrpos($matches[1], '.')), '.');
        return ctype_alpha($tld[0]);
    }

    /**
     * Validate an IP.
     *
     * @param string $ip IP address
     * @param bool $allow_private Allow private IP networks
     * @return  bool
     */
    public static function ip(string $ip, bool $allow_private = true): bool
    {
        // Do not allow reserved addresses
        $flags = FILTER_FLAG_NO_RES_RANGE;

        if ($allow_private === false) {
            // Do not allow private or reserved addresses
            $flags = $flags | FILTER_FLAG_NO_PRIV_RANGE;
        }

        return (bool) filter_var($ip, FILTER_VALIDATE_IP, $flags);
    }

    /**
     * Validates a credit card number, with a Luhn check if possible.
     *
     * @param string $number credit card number
     * @param string|array $type card type, or an array of card types
     * @return bool
     * @throws Kohana_Exception
     * @uses    Valid::luhn
     */
    public static function credit_card(string $number, $type = null): bool
    {
        // Remove all non-digit characters from the number
        if (($number = preg_replace('/\D+/', '', $number)) === '')
            return false;

        if ($type === null) {
            // Use the default type
            $type = 'default';
        } elseif (is_array($type)) {
            foreach ($type as $t) {
                // Test each type for validity
                if (Valid::credit_card($number, $t))
                    return true;
            }

            return false;
        }

        $cards = Kohana::$config->load('credit_cards');

        // Check card type
        $type = strtolower($type);

        if (!isset($cards[$type]))
            return false;

        // Check card number length
        $length = strlen($number);

        // Validate the card length by the card type
        if (!in_array($length, preg_split('/\D+/', $cards[$type]['length'])))
            return false;

        // Check card number prefix
        if (!preg_match('/^' . $cards[$type]['prefix'] . '/', $number))
            return false;

        // No Luhn check required
        if (!$cards[$type]['luhn'])
            return true;

        return Valid::luhn($number);
    }

    /**
     * Validate a number against the [Luhn](https://en.wikipedia.org/wiki/Domain_name#Domain_name_syntax)
     * (mod10) formula.
     *
     * @param mixed $number Number to check
     * @return  bool
     */
    public static function luhn($number): bool
    {
        // Force the value to be a string as this method uses string functions.
        // Converting to an integer may pass PHP_INT_MAX and result in an error!
        $number = (string) $number;

        if (!ctype_digit($number)) {
            // Luhn can only be used on numbers!
            return false;
        }

        // Check number length
        $length = strlen($number);

        // Checksum of the card number
        $checksum = 0;

        for ($i = $length - 1; $i >= 0; $i -= 2) {
            // Add up every 2nd digit, starting from the right
            $checksum += (int) substr($number, $i, 1);
        }

        for ($i = $length - 2; $i >= 0; $i -= 2) {
            // Add up every 2nd digit doubled, starting from the right
            $double = substr($number, $i, 1) * 2;

            // Subtract 9 from the double where value is greater than 10
            $checksum += $double >= 10 ? $double - 9 : $double;
        }

        // If the checksum is a multiple of 10, the number is valid
        return $checksum % 10 === 0;
    }

    /**
     * Checks if a phone number is valid.
     *
     * @param string $number Phone number to check
     * @param array|null $lengths
     * @return  bool
     */
    public static function phone(string $number, array $lengths = null): bool
    {
        if (!is_array($lengths)) {
            $lengths = [7, 10, 11];
        }

        // Remove all non-digit characters from the number
        $number = preg_replace('/\D+/', '', $number);

        // Check if the number is within range
        return in_array(strlen($number), $lengths);
    }

    /**
     * Tests if a string is a valid date string.
     *
     * @param string $str Date to check
     * @return  bool
     */
    public static function date(string $str): bool
    {
        return strtotime($str) !== false;
    }

    /**
     * Checks whether a string consists of alphabetical characters only.
     *
     * @param string $str Input string
     * @param bool $utf8 Trigger UTF-8 compatibility
     * @return  bool
     */
    public static function alpha(string $str, bool $utf8 = false): bool
    {
        if ($utf8 === true) {
            return (bool) preg_match('/^\pL++$/uD', $str);
        } else {
            return ctype_alpha($str);
        }
    }

    /**
     * Checks whether a string consists of alphabetical characters and numbers only.
     *
     * @param string $str Input string
     * @param bool $utf8 Trigger UTF-8 compatibility
     * @return  bool
     */
    public static function alpha_numeric(string $str, bool $utf8 = false): bool
    {
        if ($utf8 === true) {
            return (bool) preg_match('/^[\pL\pN]++$/uD', $str);
        } else {
            return ctype_alnum($str);
        }
    }

    /**
     * Checks whether a string consists of alphabetical characters, numbers, underscores and dashes only.
     *
     * @param string $str Input string
     * @param bool $utf8 Trigger UTF-8 compatibility
     * @return  bool
     */
    public static function alpha_dash(string $str, bool $utf8 = false): bool
    {
        if ($utf8 === true) {
            $regex = '/^[-\pL\pN_]++$/uD';
        } else {
            $regex = '/^[-a-z0-9_]++$/iD';
        }

        return (bool) preg_match($regex, $str);
    }

    /**
     * Checks whether a string consists of digits only (no dots or dashes).
     *
     * @param mixed $str Input string
     * @param bool $utf8 Trigger UTF-8 compatibility
     * @return  bool
     */
    public static function digit($str, bool $utf8 = false): bool
    {
        if ($utf8) {
            return (bool) preg_match('/^\pN++$/uD', $str);
        } else {
            return is_int($str) && $str >= 0 || ctype_digit($str);
        }
    }

    /**
     * Checks whether a string is a valid number (negative and decimal numbers allowed).
     *
     * Uses {@link https://www.php.net/manual/en/function.localeconv.php locale conversion}
     * to allow decimal point to be locale specific.
     *
     * @param mixed $str Input string
     * @return  bool
     */
    public static function numeric($str): bool
    {
        // Get the decimal point for the current locale
        list($decimal) = array_values(localeconv());

        // A lookahead is used to make sure the string contains at least one digit (before or after the decimal point)
        return (bool) preg_match('/^-?+(?=.*[0-9])[0-9]*+' . preg_quote($decimal) . '?+[0-9]*+$/D', (string) $str);
    }

    /**
     * Tests if a number is within a range.
     *
     * @param int $number Number to check
     * @param int $min Minimum value
     * @param int $max Maximum value
     * @param int|null $step Increment size
     * @return  bool
     */
    public static function range(int $number, int $min, int $max, int $step = null): bool
    {
        if ($number < $min || $number > $max) {
            // Number is outside of range
            return false;
        }

        if (!$step) {
            // Default to steps of 1
            $step = 1;
        }

        // Check step requirements
        return ($number - $min) % $step === 0;
    }

    /**
     * Checks if a string is a proper decimal format. Optionally, a specific
     * number of digits can be checked too.
     *
     * @param string $str Number to check
     * @param int $places Number of decimal places
     * @param int|null $digits Number of digits
     * @return  bool
     */
    public static function decimal(string $str, int $places = 2, ?int $digits = null): bool
    {
        if ($digits > 0) {
            // Specific number of digits
            $digits = '{' . ((int) $digits) . '}';
        } else {
            // Any number of digits
            $digits = '+';
        }

        // Get the decimal point for the current locale
        list($decimal) = array_values(localeconv());

        return (bool) preg_match('/^[+-]?[0-9]' . $digits . preg_quote($decimal) . '[0-9]{' . $places . '}$/D', $str);
    }

    /**
     * Checks if a string is a proper hexadecimal HTML color value. The validation
     * is quite flexible as it does not require an initial "#" and also allows for
     * the short notation using only three instead of six hexadecimal characters.
     *
     * @param string $str Input string
     * @return  bool
     */
    public static function color(string $str): bool
    {
        return (bool) preg_match('/^#?+[0-9a-f]{3}(?:[0-9a-f]{3})?$/iD', $str);
    }

    /**
     * Checks if a field matches the value of another field.
     *
     * @param mixed $array Array of values
     * @param string $field Field name
     * @param string $match Field name to match
     * @return  bool
     */
    public static function matches($array, string $field, string $match): bool
    {
        return $array[$field] === $array[$match];
    }

}
