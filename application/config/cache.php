<?php

return [
    'memcached' => [
        'driver' => 'memcached',
        'default_expire' => 3600,
        'servers' => [
            'local' => [
                // Memcached Server
                'host' => 'host.docker.internal',
                // Memcached port number
                'port' => 11211,
                'weight' => 1,
            ],
        ]
    ],
//    'memcache' => [
//        'driver' => 'memcache',
//        'default_expire' => 3600,
//        // Use Zlib compression (can cause issues with integers)
//        'compression' => false,
//        'servers' => [
//            'local' => [
//                // Memcache Server
//                'host' => 'localhost',
//                // Memcache port number
//                'port' => 11211,
//                // Persistent connection
//                'persistent' => false,
//                'weight' => 1,
//                'timeout' => 1,
//                'retry_interval' => 15,
//                'status' => true,
//            ],
//        ],
//        // Take server offline immediately on first fail (no retry)
//        'instant_death' => true,
//    ],
//    'memcachetag' => [
//        'driver' => 'memcachetag',
//        'default_expire' => 3600,
//        // Use Zlib compression (can cause issues with integers)
//        'compression' => false,
//        'servers' => [
//            'local' => [
//                // Memcache Server
//                'host' => 'localhost',
//                // Memcache port number
//                'port' => 11211,
//                // Persistent connection
//                'persistent' => false,
//                'weight' => 1,
//                'timeout' => 1,
//                'retry_interval' => 15,
//                'status' => true,
//            ],
//        ],
//        'instant_death' => true,
//    ],
//    'apcu' => [
//        'driver' => 'apcu',
//        'default_expire' => 3600,
//    ],
//    'apc' => [
//        'driver' => 'apc',
//        'default_expire' => 3600,
//    ],
//    'wincache' => [
//        'driver' => 'wincache',
//        'default_expire' => 3600,
//    ],
//    'sqlite' => [
//        'driver' => 'sqlite',
//        'default_expire' => 3600,
//        'database' => APPPATH . 'cache/kohana-cache.sql3',
//        'schema' => 'CREATE TABLE caches(id VARCHAR(127) PRIMARY KEY, tags VARCHAR(255), expiration INTEGER, cache TEXT)',
//    ],
//    'file' => [
//        'driver' => 'file',
//        'cache_dir' => APPPATH . 'cache',
//        'default_expire' => 3600,
//        'ignore_on_delete' => [
//            '.gitignore',
//            '.git',
//            '.svn'
//        ]
//    ]
];
