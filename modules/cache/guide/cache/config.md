# Kohana Cache configuration

Kohana Cache uses configuration groups to create cache instances. A configuration group can use any supported driver, with successive groups using multiple instances of the same driver type.

The default cache group is loaded based on the `Cache::$default` setting. It is set to the `file` driver as standard, however this can be changed within the `/application/boostrap.php` file.

    // Change the default cache driver to memcached
    Cache::$default = 'memcached';

    // Load the memcached cache driver using default setting
    $memcached = Cache::instance();

## Group settings

Below are the default cache configuration groups for each supported driver. Add to or override these settings within the `application/config/cache.php` file.

| Name           | Required | Description                              |
|----------------|----------|------------------------------------------|
| driver         | __YES__  | (_string_) The driver type to use        |
| default_expire | __NO__   | (_integer_) The default expiration value |

    'file' => [
        'driver' => 'file',
        'cache_dir' => APPPATH . 'cache',
        'default_expire' => 3600,
    ],

## Memcached settings

| Name    | Required | Description                                                                                                              |
|---------|----------|--------------------------------------------------------------------------------------------------------------------------|
| driver  | __YES__  | (_string_) The driver type to use                                                                                        |
| servers | __YES__  | (_array_) Associative array of server details, must include a __host__ key. (See _Memcached server configuration_ below) |

### Memcached server configuration

| Name    | Required | Description                                                                                                                                                                                   |
|---------|----------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| host    | __YES__  | (_string_) The host of the memcache server, i.e. __localhost__; or __127.0.0.1__; or __memcache.domain.tld__                                                                                  |
| port    | __NO__   | (_integer_) The port on which memcached is running. Set this parameter to 0 when using UNIX domain sockets. Default to __11211__                                                              |
| weight  | __NO__   | (_integer_) The weight of the server relative to the total weight of all the servers in the pool. This controls the probability of the server being selected for operations. Default to __1__ |
| options | __NO__   | (_array_) An associative array of options where the key is the option to set and the value is the new value for the option. Default to __[]__                                                 |

    'memcached' => [
        'driver' => 'memcached',
        'default_expire' => 3600,
        'servers' => [
            'local' => [
                'host' => 'localhost',
                'port' => 11211,
                'weight' => 1,
                'options' => []
            ],
        ]
    ],

## Memcache & Memcached-tag settings

| Name        | Required | Description                                                                                                             |
|-------------|----------|-------------------------------------------------------------------------------------------------------------------------|
| driver      | __YES__  | (_string_) The driver type to use                                                                                       |
| servers     | __YES__  | (_array_) Associative array of server details, must include a __host__ key. (see _Memcache server configuration_ below) |
| compression | __NO__   | (_boolean_) Use data compression when caching                                                                           |

### Memcache server configuration

| Name             | Required | Description                                                                                                                                                                                                                                                                                                                           |
|------------------|----------|---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| host             | __YES__  | (_string_) The host of the memcache server, i.e. __localhost__; or __127.0.0.1__; or __memcache.domain.tld__                                                                                                                                                                                                                          |
| port             | __NO__   | (_integer_) Point to the port where memcached is listening for connections. Set this parameter to 0 when using UNIX domain sockets. Default to __11211__                                                                                                                                                                              |
| persistent       | __NO__   | (_boolean_) Controls the use of a persistent connection. Default to __true__                                                                                                                                                                                                                                                          |
| weight           | __NO__   | (_integer_) Number of buckets to create for this server which in turn control its probability of it being selected. The probability is relative to the total weight of all servers. Default to __1__                                                                                                                                  |
| timeout          | __NO__   | (_integer_) Value in seconds which will be used for connecting to the daemon. Think twice before changing the default value of 1 second - you can lose all the advantages of caching if your connection is too slow. Default to __1__                                                                                                 |
| retry_interval   | __NO__   | (_integer_) Controls how often a failed server will be retried, the default value is 15 seconds. Setting this parameter to -1 disables automatic retry. Default to __15__                                                                                                                                                             |
| status           | __NO__   | (_boolean_) Controls if the server should be flagged as online. Default to __true__                                                                                                                                                                                                                                                   |
| failure_callback | __NO__   | (_[callback](http://www.php.net/manual/en/language.pseudo-types.php#language.types.callback)_) Allows the user to specify a callback function to run upon encountering an error. The callback is run before failover is attempted. The function takes two parameters, the hostname and port of the failed server. Default to __null__ |

    'memcache' => [
        'driver' => 'memcache',
        'default_expire' => 3600,
        // Use Zlib compression (can cause issues with integers)
        'compression' => false,
        'servers' => [
            'local' => [
                // Memcache Server
                'host' => 'localhost',
                // Memcache port number
                'port' => 11211,
                // Persistent connection
                'persistent' => false,
                'weight' => 1,
                'timeout' => 1,
                'retry_interval' => 15,
                'status' => true,
            ],
        ],
        // Take server offline immediately on first fail (no retry)
        'instant_death' => true,
    ],
    'memcachetag' => [
        'driver' => 'memcachetag',
        'default_expire' => 3600,
        // Use Zlib compression (can cause issues with integers)
        'compression' => false,
        'servers' => [
            'local' => [
                // Memcache Server
                'host' => 'localhost',
                // Memcache port number
                'port' => 11211,
                // Persistent connection
                'persistent' => false,
                'weight' => 1,
                'timeout' => 1,
                'retry_interval' => 15,
                'status' => true,
            ],
        ],
        'instant_death' => true,
    ],

## APC settings

    'apc' => [
        'driver' => 'apc',
        'default_expire' => 3600,
    ],

## APCu settings

    'apcu' => [
        'driver' => 'apcu',
        'default_expire' => 3600,
    ],

## SQLite settings

    'sqlite' => [
        'driver' => 'sqlite',
        'default_expire' => 3600,
        'database' => APPPATH . 'cache/kohana-cache.sql3',
        'schema' => 'CREATE TABLE caches(id VARCHAR(127) PRIMARY KEY,
            tags VARCHAR(255), expiration INTEGER, cache TEXT)',
    ],

## File settings

    'file' => [
        'driver' => 'file',
        'cache_dir' => APPPATH . 'cache',
        'default_expire' => 3600,
    ],

## Wincache settings

    'wincache' => [
        'driver' => 'wincache',
        'default_expire' => 3600,
    ],

## Override existing configuration group

The following example demonstrates how to override an existing configuration setting, using the config file in `/application/config/cache.php`.

    <?php

    return [
        // Override the default configuration
        'memcached' => [
            // Use Memcached as the default driver
            'driver' => 'memcached',
            // Overide default expiry
            'default_expire' => 8000,
            'servers' => [
                // Add a new server
                [
                    'host' => 'cache.domain.tld',
                    'port' => 11211,
                ],
            ]
        ],
    ];

## Add new configuration group

The following example demonstrates how to add a new configuration setting, using the config file in `/application/config/cache.php`.

    <?php

    return [
        // Override the default configuration
        'fastkv' => [
            // Use APCu as the default driver
            'driver' => 'apcu',
            // Overide default expiry
            'default_expire' => 1000,
        ],
    ];
