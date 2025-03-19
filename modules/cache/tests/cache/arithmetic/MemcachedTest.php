<?php

/**
 * @package    Kohana/Cache/Memcached
 * @group      kohana
 * @group      kohana.cache
 * @group      kohana.cache.memcached
 * @category   Test
 * @author     Tinsh <kilofox2000@gmail.com>
 * @copyright  (c) 2018 Kohana Group
 * @license    https://kohana.top/license
 */
class Kohana_CacheArithmeticMemcachedTest extends Kohana_CacheArithmeticMethodsTest
{
    /**
     * This method MUST be implemented by each driver to setup the `Cache`
     * instance for each test.
     *
     * This method should do the following tasks for each driver test:
     *
     *  - Test the Cache instance driver is available, skip test otherwise
     *  - Setup the Cache instance
     *  - Call the parent setup method, `parent::setUp()`
     *
     * @return  void
     * @throws Cache_Exception
     * @throws Kohana_Exception
     */
    public function setUp()
    {
        parent::setUp();

        if (!extension_loaded('memcached')) {
            $this->markTestSkipped('Memcached PHP Extension is not available');
        }

        if (!Kohana::$config->load('cache.memcached')) {
            Kohana::$config->load('cache')->set('memcached', [
                'driver' => 'memcached',
                'default_expire' => 3600,
                'servers' => [
                    'local' => [
                        'host' => 'localhost',
                        'port' => 11211,
                        'weight' => 1,
                    ],
                ]
            ]);
            Kohana::$config->load('cache.memcached');
        }

        $this->cache(Cache::instance('memcached'));
    }

}
