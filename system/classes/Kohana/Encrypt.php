<?php

/**
 * Kohana Encrypt provides a common interface to a variety of cryptography
 * engines, supports multiple instances of cryptography engines through a
 * grouped singleton pattern.
 *
 * @package    Kohana
 * @category   Security
 * @author     Tinsh <kilofox2000@gmail.com>
 * @copyright  (c) 2018 Kohana Group
 * @license    https://kohana.top/license
 */
abstract class Kohana_Encrypt
{
    /**
     * @var  string  Default instance name.
     */
    public static $default = 'default';

    /**
     * @var  array  Encrypt class instances.
     */
    public static $instances = [];

    /**
     * Creates a singleton instance of Encrypt. An encryption key must be
     * provided in your "encrypt" configuration file.
     *
     *     $encrypt = Encrypt::instance();
     *
     * @param string $name configuration group name
     * @param array $config configuration options
     * @return  Encrypt
     * @throws Kohana_Exception
     */
    public static function instance($name = null, array $config = null)
    {
        if ($name === null) {
            // Use the default instance name
            $name = Encrypt::$default;
        }

        if (!isset(Encrypt::$instances[$name])) {
            if ($config === null) {
                // Load the configuration data
                $config = Kohana::$config->load('encrypt')->$name;
            }

            if (!isset($config['driver'])) {
                throw new Kohana_Exception('No encryption driver is defined in the encryption configuration group: :group', [':group' => $name]);
            }

            // Set the driver class name
            $driver = 'Encrypt_' . ucfirst($config['driver']);

            // Create a new instance
            Encrypt::$instances[$name] = new $driver($name, $config);
        }

        return Encrypt::$instances[$name];
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
    abstract public function encode($data);
    /**
     * Decrypts an encoded string back to its original value.
     *
     *     $data = $encrypt->decode($data);
     *
     * @param   string  $data   Encoded string to be decrypted.
     * @return  string|false Decrypted string on success, or false on failure.
     */
    abstract public function decode($data);
}
