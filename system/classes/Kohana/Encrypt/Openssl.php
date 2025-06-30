<?php

/**
 * [Kohana Encrypt](api/Kohana_Encrypt) OpenSSL driver. Provides two-way
 * encryption of text and binary strings using the [OpenSSL](https://www.php.net/openssl)
 * extension.
 *
 * @package     Kohana
 * @category    Security
 * @author      Tinsh <kilofox2000@gmail.com>
 * @copyright   (c) 2018 Kohana Group
 * @license     https://kohana.top/license
 */
class Kohana_Encrypt_Openssl
{
    /**
     * @var string  The cipher method.
     */
    protected $method;

    /**
     * @var string  Encryption key.
     */
    protected $key;

    /**
     * @var int     Encryption key.
     */
    protected $options = 3;

    /**
     * @var string  Encryption key.
     */
    protected $tag;

    /**
     * @var string  The authentication tag when using AEAD cipher mode (GCM or CCM).
     */
    protected $aad;

    /**
     * @var string  The length of the authentication tag.
     */
    protected $tagLength;

    /**
     * @var int     The size of the Initialization Vector (IV) in bytes.
     */
    protected $ivSize;

    /**
     * @var string  The Initialization Vector for unit testing.
     */
    protected $iv;

    /**
     * Creates a new OpenSSL wrapper.
     *
     * @param string $name configuration group name
     * @param array $config configuration options
     * @throws Kohana_Exception
     */
    public function __construct($name, $config)
    {
        if (!isset($config['key'])) {
            // No default encryption key is provided!
            throw new Kohana_Exception('No encryption key is defined in the encryption configuration group: :group', [':group' => $name]);
        }

        if (!isset($config['method'])) {
            // Add the default cipher method.
            $config['method'] = 'AES-256-CTR';
        }

        // Store the cipher method and the key.
        $this->method = $config['method'];
        $this->key = $config['key'];

        // Store other parameters.
        isset($config['options']) and $this->options = $config['options'];
        isset($config['tag']) and $this->tag = $config['tag'];
        isset($config['aad']) and $this->aad = $config['aad'];
        isset($config['tagLength']) and $this->tagLength = $config['tagLength'];

        // Store the faked IV for unit testing.
        isset($config['iv']) and $this->iv = $config['iv'];

        // Store the IV size.
        $this->ivSize = openssl_cipher_iv_length($this->method);
    }

    /**
     * Encrypts a string and returns an encrypted string that can be decoded.
     *
     *     $data = $encrypt->encode($data);
     *
     * The encrypted binary data is encoded using [base64](https://www.php.net/base64_encode)
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
            // Create a random initialization vector of the proper size for the current cipher.
            $iv = openssl_random_pseudo_bytes($this->ivSize);
        }

        // Encrypt the data using the configured options and generated IV.
        if (isset($this->tag)) {
            $data = openssl_encrypt($data, $this->method, $this->key, $this->options, $iv, $this->tag, $this->aad, $this->tagLength);
        } else {
            $data = openssl_encrypt($data, $this->method, $this->key, $this->options, $iv);
        }

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
        if (isset($this->tag)) {
            return rtrim(openssl_decrypt($data, $this->method, $this->key, $this->options, $iv, $this->tag, $this->aad), "\0");
        } else {
            return rtrim(openssl_decrypt($data, $this->method, $this->key, $this->options, $iv), "\0");
        }
    }

}
