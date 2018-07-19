<?php

/**
 * [Kohana Encrypt](api/Kohana_Encrypt) Mcrypt driver. Provides two-way
 * encryption of text and binary strings using the [Mcrypt](http://php.net/mcrypt)
 * extension, which consists of three parts: the key, the cipher, and the mode.
 *
 * The Key
 * :  A secret passphrase that is used for encoding and decoding.
 *
 * The Cipher
 * :  A [cipher](http://php.net/mcrypt.ciphers) determines how the encryption
 *    is mathematically calculated. By default, the "rijndael-128" cipher
 *    is used. This is commonly known as "AES-128" and is an industry standard.
 *
 * The Mode
 * :  The [mode](http://php.net/mcrypt.constants) determines how the encrypted
 *    data is written in binary form. By default, the "nofb" mode is used,
 *    which produces short output with high entropy.
 *
 * @package     Kohana
 * @category    Security
 * @author      Kohana Team
 * @author      Tinsh <kilofox2000@gmail.com>
 * @copyright   (c) 2007-2017 Kohana Team
 * @copyright   (c) 2017-2018 Kohana Group
 * @license     https://kohana.top/license
 */
class Kohana_Encrypt_Mcrypt
{
    /**
     * @var string  RAND type to use.
     *
     * Only MCRYPT_DEV_URANDOM and MCRYPT_DEV_RANDOM are considered safe.
     * Using MCRYPT_RAND will silently revert to MCRYPT_DEV_URANDOM.
     */
    protected static $rand = MCRYPT_DEV_URANDOM;

    /**
     * @var string  Encryption key.
     */
    protected $key;

    /**
     * @var string  Mcrypt mode.
     */
    protected $mode;

    /**
     * @var string  Mcrypt cipher.
     */
    protected $cipher;

    /**
     * @var int     The size of the Initialization Vector (IV) in bytes.
     */
    protected $ivSize;

    /**
     * @var string  The Initialization Vector for unit testing.
     */
    protected $iv;

    /**
     * Creates a new mcrypt wrapper.
     *
     * @param   string  $name   Configuration group name.
     * @param   string  $config Configuration parameters.
     */
    public function __construct($name, $config)
    {
        if (!isset($config['key'])) {
            // No default encryption key is provided!
            throw new Kohana_Exception('No encryption key is defined in the encryption configuration group: :group', [':group' => $name]);
        }

        if (!isset($config['mode'])) {
            // Add the default mode.
            $config['mode'] = MCRYPT_MODE_NOFB;
        }

        if (!isset($config['cipher'])) {
            // Add the default cipher.
            $config['cipher'] = MCRYPT_RIJNDAEL_128;
        }

        // Find the max length of the key, based on cipher and mode.
        $size = mcrypt_get_key_size($config['cipher'], $config['mode']);

        if (isset($config['key'][$size])) {
            // Shorten the key to the maximum size.
            $config['key'] = substr($config['key'], 0, $size);
        } else if (PHP_VERSION_ID >= 50600) {
            $config['key'] = $this->normalizeKey($config['key'], $config['cipher'], $config['mode']);
        }

        // Store the key, mode and cipher.
        $this->key = $config['key'];
        $this->mode = $config['mode'];
        $this->cipher = $config['cipher'];

        // Store the faked IV for unit testing.
        isset($config['iv']) and $this->iv = $config['iv'];

        // Store the IV size.
        $this->ivSize = mcrypt_get_iv_size($this->cipher, $this->mode);
    }

    /**
     * Encrypts a string and returns an encrypted string that can be decoded.
     *
     *     $data = $encrypt->encode($data);
     *
     * The encrypted binary data is encoded using [base64](http://php.net/base64_encode)
     * to convert it to a string. This string can be stored in a database,
     * displayed, and passed using most other means without corruption.
     *
     * @param   string  $data   Data to be encrypted.
     * @return  string
     */
    public function encode($data)
    {
        // Use a fake random initialization vector for unit testing.
        if (isset($this->iv)) {
            $iv = $this->iv;
        } else {
            /*
             * Silently use MCRYPT_DEV_URANDOM when the chosen random number
             * generator is not one of those that are considered secure.
             *
             * Also sets Encrypt_Mcrypt::$rand to MCRYPT_DEV_URANDOM when it's
             * not already set.
             */
            if (self::$rand !== MCRYPT_DEV_URANDOM && self::$rand !== MCRYPT_DEV_RANDOM) {
                self::$rand = MCRYPT_DEV_URANDOM;
            }

            // Create a random initialization vector of the proper size for the current cipher.
            $iv = mcrypt_create_iv($this->ivSize, self::$rand);
        }

        // Encrypt the data using the configured options and generated IV.
        $data = mcrypt_encrypt($this->cipher, $this->key, $data, $this->mode, $iv);

        // Use base64 encoding to convert to a string.
        return base64_encode($iv . $data);
    }

    /**
     * Decrypts an encoded string back to its original value.
     *
     *     $data = $encrypt->decode($data);
     *
     * @param   string  $data   Encoded string to be decrypted.
     * @return  false   If decryption fails.
     * @return  string
     */
    public function decode($data)
    {
        // Convert the data back to binary.
        $data = base64_decode($data, true);

        if (!$data) {
            // Invalid base64 data.
            return false;
        }

        // Extract the initialization vector from the data.
        $iv = substr($data, 0, $this->ivSize);

        if ($this->ivSize !== strlen($iv)) {
            // The IV is not the expected size.
            return false;
        }

        // Remove the IV from the data.
        $data = substr($data, $this->ivSize);

        // Return the decrypted data, trimming the \0 padding bytes from the end of the data.
        return rtrim(mcrypt_decrypt($this->cipher, $this->key, $data, $this->mode, $iv), "\0");
    }

    /**
     * Normalize key for PHP 5.6 for backwards compatibility.
     *
     * This method is a shim to make PHP 5.6 behave in a B/C way for
     * legacy key padding when shorter-than-supported keys are used.
     *
     * @param   string  $key    Encryption key.
     * @param   string  $cipher Mcrypt cipher.
     * @param   string  $mode   Mcrypt mode.
     */
    protected function normalizeKey($key, $cipher, $mode)
    {
        // Open the cipher.
        $td = mcrypt_module_open($cipher, '', $mode, '');

        // Loop through the supported key sizes.
        foreach (mcrypt_enc_get_supported_key_sizes($td) as $supported) {
            // If key is short, needs padding.
            if (strlen($key) <= $supported) {
                return str_pad($key, $supported, "\0");
            }
        }

        // At this point key must be greater than max supported size, shorten it.
        return substr($key, 0, mcrypt_get_key_size($cipher, $mode));
    }

}
