<?php

/**
 * Kohana Cache provides a common interface to a variety of caching engines. Tags are
 * supported where available natively to the cache system. Kohana Cache supports multiple
 * instances of cache engines through a grouped singleton pattern.
 *
 * ### Supported cache engines
 *
 * *  [APCu](https://www.php.net/manual/en/book.apcu.php)
 * *  File
 * *  [Memcached](https://www.php.net/manual/en/book.memcached.php)
 * *  [Memcache](https://www.php.net/manual/en/book.memcache.php)
 * *  [Memcached-tags](https://code.google.com/archive/p/memcached-tags/)
 * *  [SQLite](https://www.php.net/manual/en/ref.pdo-sqlite.php)
 *
 * ### Introduction to caching
 *
 * Caching should be implemented with consideration. Generally, caching the result of resources
 * is faster than reprocessing them. Choosing what, how and when to cache is vital. PHP APCu is
 * presently one of the fastest caching systems available, closely followed by Memcached. SQLite
 * and File caching are two of the slowest cache methods, however usually faster than reprocessing
 * a complex set of instructions.
 *
 * Caching engines that use memory are considerably faster than the file based alternatives. But
 * memory is limited whereas disk space is plentiful. If caching large datasets it is best to use
 * file caching.
 *
 * ### Configuration settings
 *
 * Kohana Cache uses configuration groups to create cache instances. A configuration group can
 * use any supported driver, with successive groups using the same driver type if required.
 *
 * #### Configuration example
 *
 * Below is an example of a _memcached_ server configuration.
 *
 *     return [
 *         // Name of group
 *         'memcached' => [
 *             // Using Memcached driver
 *             'driver' => 'memcached',
 *             // Available server definitions
 *             'servers' => [
 *                 [
 *                     'host' => 'localhost',
 *                     'port' => 11211,
 *                     'weight' => 1,
 *                     'options' => [],
 *                 ]
 *             ],
 *         ],
 *     ]
 *
 * In cases where only one cache group is required, set `Cache::$default` (in your bootstrap,
 * or by extending `Kohana_Cache` class) to the name of the group, and use:
 *
 *     $cache = Cache::instance(); // instead of Cache::instance('memcached')
 *
 * It will return the cache instance of the group it has been set in `Cache::$default`.
 *
 * #### General cache group configuration settings
 *
 * Below are the settings available to all types of cache driver.
 *
 * Name           | Required | Description
 * -------------- | -------- | ---------------------------------------------------------------
 * driver         | __YES__  | (_string_) The driver type to use
 *
 * Details of the settings specific to each driver are available within the driver's documentation.
 *
 * ### System requirements
 *
 * *  Kohana 3.0.x
 * *  PHP 5.2.4 or greater
 *
 * @package    Kohana/Cache
 * @category   Base
 * @version    2.0
 * @author     Kohana Team
 * @copyright  (c) 2009-2012 Kohana Team
 * @license    https://kohana.top/license
 */
abstract class Kohana_Cache
{
    const DEFAULT_EXPIRE = 3600;

    /**
     * @var   string     default driver to use
     */
    public static $default = 'file';

    /**
     * @var   Kohana_Cache instances
     */
    public static $instances = [];

    /**
     * Creates a singleton of a Kohana Cache group. If no group is supplied
     * the __default__ cache group is used.
     *
     *     // Create an instance of the default group
     *     $default_group = Cache::instance();
     *
     *     // Create an instance of a group
     *     $foo_group = Cache::instance('foo');
     *
     *     // Access an instantiated group directly
     *     $foo_group = Cache::$instances['default'];
     *
     * @param string $group the name of the cache group to use [Optional]
     * @return  Cache
     * @throws Cache_Exception
     * @throws Kohana_Exception
     */
    public static function instance($group = null)
    {
        // If there is no group supplied
        if ($group === null) {
            // Use the default setting
            $group = Cache::$default;
        }

        if (isset(Cache::$instances[$group])) {
            // Return the current group if initiated already
            return Cache::$instances[$group];
        }

        $config = Kohana::$config->load('cache');

        if (!$config->offsetExists($group)) {
            throw new Cache_Exception('Failed to load Kohana Cache group: :group', [':group' => $group]);
        }

        $config = $config->get($group);

        // Create a new cache type instance
        $cache_class = 'Cache_' . ucfirst($config['driver']);
        Cache::$instances[$group] = new $cache_class($config);

        // Return the instance
        return Cache::$instances[$group];
    }

    /**
     * @var  Config
     */
    protected $_config = [];

    /**
     * Ensures singleton pattern is observed, loads the default expiry
     *
     * @param  array  $config  configuration
     */
    protected function __construct(array $config)
    {
        $this->config($config);
    }

    /**
     * Getter and setter for the configuration. If no argument provided, the
     * current configuration is returned. Otherwise, the configuration is set
     * to this class.
     *
     *     // Overwrite all configuration
     *     $cache->config(['driver' => 'memcached', '...']);
     *
     *     // Set a new configuration setting
     *     $cache->config('servers', ['foo' => 'bar', '...']);
     *
     *     // Get a configuration setting
     *     $servers = $cache->config('servers');
     *
     * @param mixed $key key to set to array, either array or config path
     * @param mixed $value value to associate with key
     * @return  mixed
     */
    public function config($key = null, $value = null)
    {
        if ($key === null)
            return $this->_config;

        if (is_array($key)) {
            $this->_config = $key;
        } else {
            if ($value === null)
                return Arr::get($this->_config, $key);

            $this->_config[$key] = $value;
        }

        return $this;
    }

    /**
     * Overload the __clone() method to prevent cloning
     *
     * @return  void
     * @throws  Cache_Exception
     */
    final public function __clone()
    {
        throw new Cache_Exception('Cloning of Kohana_Cache objects is forbidden');
    }

    /**
     * Retrieve a cached value entry by id.
     *
     *     // Retrieve cache entry from default group
     *     $data = Cache::instance()->get('foo');
     *
     *     // Retrieve cache entry from default group and return 'bar' if missing
     *     $data = Cache::instance()->get('foo', 'bar');
     *
     *     // Retrieve cache entry from memcached group
     *     $data = Cache::instance('memcached')->get('foo');
     *
     * @param   string  $id       id of cache to entry
     * @param   string  $default  default value to return if cache miss
     * @return  mixed
     * @throws  Cache_Exception
     */
    abstract public function get($id, $default = null);
    /**
     * Set a value to cache with id and lifetime
     *
     *     $data = 'bar';
     *
     *     // Set 'bar' to 'foo' in default group, using default expiry
     *     Cache::instance()->set('foo', $data);
     *
     *     // Set 'bar' to 'foo' in default group for 30 seconds
     *     Cache::instance()->set('foo', $data, 30);
     *
     *     // Set 'bar' to 'foo' in memcached group for 10 minutes
     *     if (Cache::instance('memcached')->set('foo', $data, 600)) {
     *          // Cache was set successfully
     *          return;
     *     }
     *
     * @param   string   $id        id of cache entry
     * @param   string   $data      data to set to cache
     * @param   int $lifetime lifetime in seconds
     * @return  bool
     */
    abstract public function set($id, $data, $lifetime = 3600);
    /**
     * Delete a cache entry based on id
     *
     *     // Delete 'foo' entry from the default group
     *     Cache::instance()->delete('foo');
     *
     *     // Delete 'foo' entry from the memcached group
     *     Cache::instance('memcached')->delete('foo');
     *
     * @param   string  $id  id to remove from cache
     * @return  bool
     */
    abstract public function delete($id);
    /**
     * Delete all cache entries.
     *
     * Beware of using this method when
     * using shared memory cache systems, as it will wipe every
     * entry within the system for all clients.
     *
     *     // Delete all cache entries in the default group
     *     Cache::instance()->delete_all();
     *
     *     // Delete all cache entries in the memcached group
     *     Cache::instance('memcached')->delete_all();
     *
     * @return bool
     */
    abstract public function delete_all();
    /**
     * Replaces troublesome characters with underscores.
     *
     *     // Sanitize a cache id
     *     $id = $this->_sanitize_id($id);
     *
     * @param   string  $id  id of cache to sanitize
     * @return  string
     */
    protected function _sanitize_id($id)
    {
        // Change slashes and spaces to underscores
        return str_replace(['/', '\\', ' '], '_', $id);
    }

}
