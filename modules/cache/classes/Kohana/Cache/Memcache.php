<?php

/**
 * [Kohana Cache](api/Kohana_Cache) Memcache driver,
 *
 * ### Supported cache engines
 *
 * *  [Memcache](https://www.php.net/manual/en/book.memcache.php)
 * *  [Memcached-tags](https://code.google.com/archive/p/memcached-tags/)
 *
 * ### Configuration example
 *
 * Below is an example of a _memcache_ server configuration.
 *
 *     return [
 *         // Default group
 *         'default' => [
 *             // Using Memcache driver
 *             'driver' => 'memcache',
 *             // Available server definitions
 *             'servers' => [
 *                 // First memcache server
 *                 [
 *                     'host' => 'localhost',
 *                     'port' => 11211,
 *                     'persistent' => false,
 *                     'weight' => 1,
 *                     'timeout' => 1,
 *                     'retry_interval' => 15,
 *                     'status' => true,
 *                     'instant_death' => true,
 *                     'failure_callback' => ['className', 'classMethod']
 *                 ],
 *                 // Second memcache server
 *                 [
 *                     'host' => '192.168.1.5',
 *                     'port' => 22122,
 *                     'persistent' => true
 *                 ]
 *             ],
 *             // Use compression?
 *             'compression' => false,
 *         ],
 *     ];
 *
 * In cases where only one cache group is required, if the group is named `default` there is
 * no need to pass the group name when instantiating a cache instance.
 *
 * #### General cache group configuration settings
 *
 * Below are the settings available to all types of cache driver.
 *
 * Name           | Required | Description
 * -------------- | -------- | ---------------------------------------------------------------
 * driver         | __YES__  | (_string_) The driver type to use
 * servers        | __YES__  | (_array_) Associative array of server details, must include a __host__ key. (see _Memcache server configuration_ below)
 * compression    | __NO__   | (_boolean_) Use data compression when caching
 *
 * #### Memcache server configuration
 *
 * The following settings should be used when defining each memcache server
 *
 * Name             | Required | Description
 * ---------------- | -------- | ---------------------------------------------------------------
 * host             | __YES__  | (_string_) The host of the memcache server, i.e. __localhost__; or __127.0.0.1__; or __memcache.domain.tld__
 * port             | __NO__   | (_integer_) Point to the port where memcached is listening for connections. Set this parameter to 0 when using UNIX domain sockets.  Default __11211__
 * persistent       | __NO__   | (_boolean_) Controls the use of a persistent connection. Default __true__
 * weight           | __NO__   | (_integer_) Number of buckets to create for this server which in turn control its probability of it being selected. The probability is relative to the total weight of all servers. Default __1__
 * timeout          | __NO__   | (_integer_) Value in seconds which will be used for connecting to the daemon. Think twice before changing the default value of 1 second - you can lose all the advantages of caching if your connection is too slow. Default __1__
 * retry_interval   | __NO__   | (_integer_) Controls how often a failed server will be retried, the default value is 15 seconds. Setting this parameter to -1 disables automatic retry. Default __15__
 * status           | __NO__   | (_boolean_) Controls if the server should be flagged as online. Default __true__
 * failure_callback | __NO__   | (_[callback](https://www.php.net/manual/en/language.types.callable.php)_) Allows the user to specify a callback function to run upon encountering an error. The callback is run before failover is attempted. The function takes two parameters, the hostname and port of the failed server. Default __null__
 *
 * ### System requirements
 *
 * *  Kohana 3.0.x
 * *  PHP 5.2.4 or greater
 * *  Memcache (plus Memcached-tags for native tagging support)
 * *  Zlib
 *
 * @package    Kohana/Cache
 * @category   Base
 * @version    2.0
 * @author     Kohana Team
 * @copyright  (c) 2009-2012 Kohana Team
 * @license    https://kohana.top/license
 * @deprecated 3.4.0
 */
class Kohana_Cache_Memcache extends Cache implements Cache_Arithmetic
{
    // Memcache has a maximum cache lifetime of 30 days
    const CACHE_CEILING = 2592000;

    /**
     * Memcache resource
     *
     * @var Memcache
     */
    protected $_memcache;

    /**
     * Flags to use when storing values
     *
     * @var string
     */
    protected $_flags;

    /**
     * The default configuration for the memcached server
     *
     * @var array
     */
    protected $_default_config = [];

    /**
     * Constructs the memcache Kohana_Cache object
     *
     * @param   array  $config  configuration
     * @throws  Cache_Exception
     */
    protected function __construct(array $config)
    {
        // Check for the memcache extension
        if (!extension_loaded('memcache')) {
            throw new Cache_Exception('Memcache PHP extension not loaded');
        }

        parent::__construct($config);

        // Setup Memcache
        $this->_memcache = new Memcache;

        // Load servers from configuration
        $servers = Arr::get($this->_config, 'servers');

        if (!$servers) {
            // Throw an exception if no server found
            throw new Cache_Exception('No Memcache servers defined in configuration');
        }

        // Setup default server configuration
        $this->_default_config = [
            'host' => 'localhost',
            'port' => 11211,
            'persistent' => false,
            'weight' => 1,
            'timeout' => 1,
            'retry_interval' => 15,
            'status' => true,
            'instant_death' => true,
            'failure_callback' => [$this, '_failed_request'],
        ];

        // Add the memcache servers to the pool
        foreach ($servers as $server) {
            // Merge the defined config with defaults
            $server += $this->_default_config;

            if (!$this->_memcache->addServer($server['host'], $server['port'], $server['persistent'], $server['weight'], $server['timeout'], $server['retry_interval'], $server['status'], $server['failure_callback'])) {
                throw new Cache_Exception('Memcache could not connect to host \':host\' using port \':port\'', [':host' => $server['host'], ':port' => $server['port']]);
            }
        }

        // Set up the flags
        $this->_flags = Arr::get($this->_config, 'compression', false) ? MEMCACHE_COMPRESSED : false;
    }

    /**
     * Retrieve a cached value entry by id.
     *
     *     // Retrieve cache entry from memcache group
     *     $data = Cache::instance('memcache')->get('foo');
     *
     *     // Retrieve cache entry from memcache group and return 'bar' if missing
     *     $data = Cache::instance('memcache')->get('foo', 'bar');
     *
     * @param   string  $id       id of cache to entry
     * @param   string  $default  default value to return if cache miss
     * @return  mixed
     * @throws  Cache_Exception
     */
    public function get($id, $default = null)
    {
        // Get the value from Memcache
        $value = $this->_memcache->get($this->_sanitize_id($id));

        // If the value wasn't found, normalise it
        if ($value === false) {
            $value = (null === $default) ? null : $default;
        }

        // Return the value
        return $value;
    }

    /**
     * Set a value to cache with id and lifetime
     *
     *     $data = 'bar';
     *
     *     // Set 'bar' to 'foo' in memcache group for 10 minutes
     *     if (Cache::instance('memcache')->set('foo', $data, 600))
     *     {
     *          // Cache was set successfully
     *          return
     *     }
     *
     * @param   string   $id        id of cache entry
     * @param   mixed    $data      data to set to cache
     * @param   integer  $lifetime  lifetime in seconds, maximum value 2592000
     * @return  boolean
     */
    public function set($id, $data, $lifetime = 3600)
    {
        // If the lifetime is greater than the ceiling
        if ($lifetime > Cache_Memcache::CACHE_CEILING) {
            // Set the lifetime to maximum cache time
            $lifetime = Cache_Memcache::CACHE_CEILING + time();
        }
        // Else if the lifetime is greater than zero
        elseif ($lifetime > 0) {
            $lifetime += time();
        }
        // Else
        else {
            // Normalise the lifetime
            $lifetime = 0;
        }

        // Set the data to memcache
        return $this->_memcache->set($this->_sanitize_id($id), $data, $this->_flags, $lifetime);
    }

    /**
     * Delete a cache entry based on id
     *
     *     // Delete the 'foo' cache entry immediately
     *     Cache::instance('memcache')->delete('foo');
     *
     *     // Delete the 'bar' cache entry after 30 seconds
     *     Cache::instance('memcache')->delete('bar', 30);
     *
     * @param   string   $id       id of entry to delete
     * @param   integer  $timeout  timeout of entry, if zero item is deleted immediately, otherwise the item will delete after the specified value in seconds
     * @return  boolean
     */
    public function delete($id, $timeout = 0)
    {
        // Delete the id
        return $this->_memcache->delete($this->_sanitize_id($id), $timeout);
    }

    /**
     * Delete all cache entries.
     *
     * Beware of using this method when
     * using shared memory cache systems, as it will wipe every
     * entry within the system for all clients.
     *
     *     // Delete all cache entries in the default group
     *     Cache::instance('memcache')->delete_all();
     *
     * @return  boolean
     */
    public function delete_all()
    {
        $result = $this->_memcache->flush();

        // We must sleep after flushing, or overwriting will not work!
        // @see https://www.php.net/manual/en/memcache.flush.php#81420
        sleep(1);

        return $result;
    }

    /**
     * Callback method for Memcache::failure_callback to use if any Memcache call
     * on a particular server fails. This method switches off that instance of the
     * server if the configuration setting `instant_death` is set to `true`.
     *
     * @param   string   $hostname
     * @param   integer  $port
     * @return  void|boolean
     * @since   3.0.8
     */
    public function _failed_request($hostname, $port)
    {
        if (!$this->_config['instant_death'])
            return;

        // Setup non-existent host
        $host = false;

        // Get host settings from configuration
        foreach ($this->_config['servers'] as $server) {
            // Merge the defaults, since they won't always be set
            $server += $this->_default_config;
            // We're looking at the failed server
            if ($hostname == $server['host'] and $port == $server['port']) {
                // Server to disable, since it failed
                $host = $server;
            }
        }

        if (!$host)
            return;
        else {
            return $this->_memcache->setServerParams(
                    $host['host'], $host['port'], $host['timeout'], $host['retry_interval'],
                    // Server is offline
                    false, [$this, '_failed_request']
            );
        }
    }

    /**
     * Increments a given value by the step value supplied.
     * Useful for shared counters and other persistent integer based
     * tracking.
     *
     * @param   string    id of cache entry to increment
     * @param   int       step value to increment by
     * @return  integer
     * @return  boolean
     */
    public function increment($id, $step = 1)
    {
        return $this->_memcache->increment($id, $step);
    }

    /**
     * Decrements a given value by the step value supplied.
     * Useful for shared counters and other persistent integer based
     * tracking.
     *
     * @param   string    id of cache entry to decrement
     * @param   int       step value to decrement by
     * @return  integer
     * @return  boolean
     */
    public function decrement($id, $step = 1)
    {
        return $this->_memcache->decrement($id, $step);
    }

}
