<?php

/**
 * Security helper class.
 *
 * @package    Kohana
 * @category   Security
 * @author     Kohana Team
 * @copyright  (c) 2007-2012 Kohana Team
 * @license    https://kohana.top/license
 */
class Kohana_Security
{
    /**
     * @var  string  key name used for token storage
     */
    public static $token_name = 'security_token';

    /**
     * Generate and store a unique token which can be used to help prevent
     * [CSRF](https://en.wikipedia.org/wiki/Cross-site_request_forgery) attacks.
     *
     *     $token = Security::token();
     *
     * You can insert this token into your forms as a hidden field:
     *
     *     echo Form::hidden('csrf', Security::token());
     *
     * And then check it when using [Validation]:
     *
     *     $array->rules('csrf', [
     *         ['not_empty'],
     *         ['Security::check'],
     *     ]);
     *
     * This provides a basic, but effective, method of preventing CSRF attacks.
     *
     * @param bool $new force a new token to be generated?
     * @return  string
     * @throws Kohana_Exception
     * @uses    Session::instance
     */
    public static function token(bool $new = false)
    {
        $session = Session::instance();

        // Get the current token
        $token = $session->get(Security::$token_name);

        if ($new === true || !$token) {
            // Generate a new unique token
            if (function_exists('openssl_random_pseudo_bytes')) {
                // Generate a random pseudo bytes token if openssl_random_pseudo_bytes is available
                // This is more secure than uniqid, because uniqid relies on microtime, which is predictable
                $token = base64_encode(openssl_random_pseudo_bytes(32));
            } else {
                // Otherwise, fall back to a hashed uniqid
                $token = sha1(uniqid(null, true));
            }

            // Store the new token
            $session->set(Security::$token_name, $token);
        }

        return $token;
    }

    /**
     * Check that the given token matches the currently stored security token.
     *
     *     if (Security::check($token))
     *     {
     *         // Pass
     *     }
     *
     * @param string $token token to check
     * @return bool
     * @throws Kohana_Exception
     * @uses    Security::token
     */
    public static function check(string $token)
    {
        return Security::slow_equals(Security::token(), $token);
    }

    /**
     * Compare two hashes in a time-invariant manner.
     * Prevents cryptographic side-channel attacks (timing attacks, specifically)
     *
     * @param string $a cryptographic hash
     * @param string $b cryptographic hash
     * @return bool
     */
    public static function slow_equals(string $a, string $b)
    {
        $diff = strlen($a) ^ strlen($b);
        for ($i = 0; $i < strlen($a) && $i < strlen($b); $i++) {
            $diff |= ord($a[$i]) ^ ord($b[$i]);
        }
        return $diff === 0;
    }

    /**
     * Encodes PHP tags in a string.
     *
     *     $str = Security::encode_php_tags($str);
     *
     * @param string $str String to sanitize
     * @return  string
     */
    public static function encode_php_tags(string $str)
    {
        return str_replace(['<?', '?>'], ['&lt;?', '?&gt;'], $str);
    }

}
