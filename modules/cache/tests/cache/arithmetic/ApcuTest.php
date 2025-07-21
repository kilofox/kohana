<?php

include_once(Kohana::find_file('tests/cache/arithmetic', 'CacheArithmeticMethods'));

/**
 * @package    Kohana/Cache
 * @group      kohana
 * @group      kohana.cache
 * @category   Test
 * @author     Kohana Team
 * @copyright  (c) 2009-2012 Kohana Team
 * @license    https://kohana.top/license
 */
class Kohana_ApcuTest extends Kohana_CacheArithmeticMethodsTest
{
    /**
     * This method MUST be implemented by each driver to set up the `Cache`
     * instance for each test.
     *
     * This method should do the following tasks for each driver test:
     *
     *  - Test the Cache instance driver is available, skip test otherwise
     *  - Set up the Cache instance
     *  - Call the parent setup method, `parent::setUp()`
     *
     * @return  void
     * @throws Cache_Exception
     * @throws Kohana_Exception
     */
    public function setUp()
    {
        parent::setUp();

        if (!extension_loaded('apcu')) {
            $this->markTestSkipped('APCu PHP Extension is not available');
        }

        if (!(ini_get('apc.enabled') AND ini_get('apc.enable_cli'))) {
            $this->markTestSkipped('APCu is not enabled. To fix ' .
                'set "apc.enabled=1" and "apc.enable_cli=1" in your php.ini file');
        }

        if (!Kohana::$config->load('cache.apcu')) {
            Kohana::$config->load('cache')
                ->set(
                    'apcu', [
                    'driver' => 'apcu',
                    'default_expire' => 3600,
                    ]
            );
        }

        $this->cache(Cache::instance('apcu'));
    }

    /**
     * Tests the [Cache::set()] method, testing;
     *
     *  - The value is cached
     *  - The lifetime is respected
     *  - The returned value type is as expected
     *  - The default not-found value is respected
     *
     * This test doesn't test the TTL as there is a known bug/feature
     * in APCu that prevents the same request from killing cache on timeout.
     *
     * @link https://bugs.php.net/bug.php?id=58832
     *
     * @dataProvider provider_set_get
     *
     * @param array $data data
     * @param mixed $expected expected
     * @return  void
     * @throws Cache_Exception
     */
    public function test_set_get(array $data, $expected)
    {
        if ($data['wait'] !== false) {
            $this->markTestSkipped('Unable to perform TTL test in CLI, see: ' .
                'https://bugs.php.net/bug.php?id=58832 for more info!');
        }

        parent::test_set_get($data, $expected);
    }

}
