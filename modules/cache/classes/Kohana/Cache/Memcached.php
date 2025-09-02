<?php

/**
 * [Kohana Cache](api/Kohana_Cache) Memcached driver. Provides a memcached based
 * driver for the Kohana Cache library.
 *
 * ### Supported cache engines
 *
 * *  [Memcached](https://www.php.net/manual/en/book.memcached.php)
 *
 * ### Configuration example
 *
 * Below is an example of a _memcached_ server configuration.
 *
 *     return [
 *         // Default group
 *         'default' => [
 *             // Using Memcached driver
 *             'driver' => 'memcached',
 *             // Available server definitions
 *             'servers' => [
 *                 // First memcached server
 *                 [
 *                     'host' => 'localhost',
 *                     'port' => 11211,
 *                     'weight' => 1,
 *                     'options' => []
 *                 ],
 *                 // Second memcached server
 *                 [
 *                     'host' => '192.168.1.5',
 *                     'port' => 22122,
 *                     'options' => [
 *                         Memcached::OPT_COMPRESSION => false,
 *                     ]
 *                 ]
 *             ]
 *         ],
 *     ];
 *
 * In cases where only one cache group is required, if the group is named
 * `default` there is no need to pass the group name when instantiating a cache
 * instance.
 *
 * #### General cache group configuration settings
 *
 * Below are the settings available to a memcached driver.
 *
 * Name           | Required | Description
 * -------------- | -------- | ---------------------------------------------------------------
 * driver         | __YES__  | (_string_) The driver type to use
 * default_expire | __NO__   | (_integer_) The default expiration value
 * servers        | __YES__  | (_array_) Associative array of server details, must include a __host__ key. (See _Memcached server configuration_ below)
 *
 * #### Memcached server configuration
 *
 * The following settings should be used when defining each memcached server.
 *
 * Name             | Required | Description
 * ---------------- | -------- | ---------------------------------------------------------------
 * host             | __YES__  | (_string_) The host of the memcached server, i.e. __localhost__; or __127.0.0.1__; or __memcached.domain.tld__
 * port             | __NO__   | (_integer_) The port on which memcached is running. Set this parameter to 0 when using UNIX domain sockets. Default to __11211__
 * weight           | __NO__   | (_integer_) The weight of the server relative to the total weight of all the servers in the pool. This controls the probability of the server being selected for operations. Default to __1__
 * options          | __NO__   | (_array_) An associative array of options where the key is the option to set and the value is the new value for the option. Default to __array()__
 *
 * ### System requirements
 *
 * *  Memcached
 * *  Memcached PHP extension
 *
 * @package    Kohana/Cache
 * @category   Base
 * @author     Loong <loong2460@gmail.com>
 * @copyright  (c) 2018 Kohana Group
 * @license    https://kohana.top/license
 */
class Kohana_Cache_Memcached extends Cache implements Cache_Arithmetic
{
    // Memcached has a maximum cache lifetime of 30 days.
    const CACHE_CEILING = 2592000;

    /**
     * Memcached resource.
     *
     * @var Memcached
     */
    protected $memcached;

    /**
     * The default configuration for the memcached server.
     *
     * @var array
     */
    protected $defaultConfig = [];

    /**
     * Memcached options.
     *
     * @var array
     */
    protected $options = [];

    /**
     * Construct the memcached cache driver. This method cannot be invoked
     * externally. The file cache driver must be instantiated using the
     * `Cache::instance()` method.
     *
     * @param   array  $config  Configuration
     * @throws  Cache_Exception
     */
    protected function __construct(array $config)
    {
        // Check for the memcached extension.
        if (!extension_loaded('memcached')) {
            throw new Cache_Exception('Memcached PHP extension not loaded');
        }

        parent::__construct($config);

        // Setup Memcached.
        $this->memcached = new Memcached;

        // Load servers from configuration.
        $servers = Arr::get($this->_config, 'servers');

        if (!$servers) {
            // Throw an exception if no server found.
            throw new Cache_Exception('No Memcached servers defined in configuration');
        }

        // Setup default server configuration.
        $this->defaultConfig = [
            'host' => 'localhost',
            'port' => 11211,
            'weight' => 1,
            'options' => []
        ];

        // Add the memcached servers to the pool.
        foreach ($servers as $server) {
            // Merge the defined config with defaults.
            $server += $this->defaultConfig;

            if (!$this->memcached->addServer($server['host'], $server['port'], $server['weight'])) {
                throw new Cache_Exception('Memcached could not connect to host \':host\' using port \':port\'', [
                ':host' => $server['host'],
                ':port' => $server['port']
                ]);
            }

            // Set options.
            if ($server['options']) {
                $this->memcached->setOptions($server['options']);
            }
        }
    }

    /**
     * Retrieve a cached value entry by id.
     *
     *     // Retrieve cache entry from memcached group.
     *     $data = Cache::instance('memcached')->get('foo');
     *
     *     // Retrieve cache entry from memcached group and return 'bar' if missing.
     *     $data = Cache::instance('memcached')->get('foo', 'bar');
     *
     * @param string $id ID of cache entry.
     * @param string|null $default Default value to return if cache miss.
     * @return  mixed
     */
    public function get(string $id, string $default = null)
    {
        // Get the value from Memcached.
        $value = $this->memcached->get($this->_sanitize_id($id));

        // If the value wasn't found, normalise it.
        if ($value === false) {
            $value = null === $default ? null : $default;
        }

        // Return the value.
        return $value;
    }

    /**
     * Set a value to cache with id and lifetime.
     *
     *     $data = 'bar';
     *
     *     // Set 'bar' to 'foo' in memcached group for 10 minutes.
     *     if (Cache::instance('memcached')->set('foo', $data, 600)) {
     *          // Cache was set successfully.
     *          return true;
     *     }
     *
     * @param string $id ID of cache entry.
     * @param   mixed    $data      Data to set to cache.
     * @param int|null $lifetime Lifetime in seconds, maximum value 2592000.
     * @return  bool
     */
    public function set(string $id, $data, int $lifetime = null): bool
    {
        // If lifetime is null, set to the default expiry.
        if ($lifetime === null) {
            $lifetime = Arr::get($this->_config, 'default_expire', Cache::DEFAULT_EXPIRE);
        }

        // If the lifetime is greater than the ceiling.
        if ($lifetime > Cache_Memcached::CACHE_CEILING) {
            // Set the lifetime to maximum cache time.
            $lifetime = Cache_Memcached::CACHE_CEILING + time();
        }
        // Else if the lifetime is greater than zero.
        elseif ($lifetime > 0) {
            $lifetime += time();
        }
        // Else
        else {
            // Normalise the lifetime.
            $lifetime = 0;
        }

        // Set the data to memcached.
        return $this->memcached->set($this->_sanitize_id($id), $data, $lifetime);
    }

    /**
     * Delete a cache entry based on id.
     *
     *     // Delete the 'foo' cache entry immediately.
     *     Cache::instance('memcached')->delete('foo');
     *
     *     // Delete the 'bar' cache entry after 30 seconds.
     *     Cache::instance('memcached')->delete('bar', 30);
     *
     * @param string $id ID of cache entry to delete.
     * @param int $time The amount of time the server will wait to delete the entry.
     * @return  bool
     */
    public function delete(string $id, int $time = 0): bool
    {
        return $this->memcached->delete($this->_sanitize_id($id), $time);
    }

    /**
     * Delete all cache entries.
     *
     * Beware of using this method when using shared memory cache systems, as it
     * will wipe every entry within the system for all clients.
     *
     *     // Delete all cache entries in the default group.
     *     Cache::instance('memcached')->delete_all();
     *
     * @return  bool
     */
    public function delete_all(): bool
    {
        return $this->memcached->flush();
    }

    /**
     * Increment a given value by the step value supplied.
     * Useful for shared counters and other persistent integer based tracking.
     *
     * @param string $id ID of cache entry to increment.
     * @param int $step Step value to increment by.
     * @return int|bool
     */
    public function increment(string $id, int $step = 1)
    {
        return $this->memcached->increment($id, $step);
    }

    /**
     * Decrement a given value by the step value supplied.
     * Useful for shared counters and other persistent integer based tracking.
     *
     * @param string $id ID of cache entry to decrement.
     * @param int $step Step value to decrement by.
     * @return int|bool
     */
    public function decrement(string $id, int $step = 1)
    {
        return $this->memcached->decrement($id, $step);
    }

}
