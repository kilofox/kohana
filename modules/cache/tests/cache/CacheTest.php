<?php

/**
 * @package    Kohana/Cache
 * @group      kohana
 * @group      kohana.cache
 * @category   Test
 * @author     Kohana Team
 * @copyright  (c) 2009-2012 Kohana Team
 * @license    https://kohana.top/license
 */
class Kohana_CacheTest extends PHPUnit\Framework\TestCase
{
    const BAD_GROUP_DEFINITION = 1010;
    const EXPECT_SELF = 1001;

    /**
     * Data provider for test_instance
     *
     * @return  array
     * @throws Cache_Exception
     * @throws Kohana_Exception
     */
    public function provider_instance()
    {
        $base = [];

        if (Kohana::$config->load('cache.file')) {
            $base = [
                // Test default group
                [
                    null,
                    Cache::instance('file')
                ],
                // Test defined group
                [
                    'file',
                    Cache::instance('file')
                ],
            ];
        }

        return $base + [
                // Test bad group definition
                [Kohana_CacheTest::BAD_GROUP_DEFINITION, 'Failed to load Kohana Cache group: 1010'],
            ];
    }

    /**
     * Tests the [Cache::factory()] method behaves as expected
     *
     * @dataProvider provider_instance
     *
     * @param $group
     * @param $expected
     * @return  void
     * @throws Cache_Exception
     * @throws Kohana_Exception
     */
    public function test_instance($group, $expected)
    {
        if ($group === Kohana_CacheTest::BAD_GROUP_DEFINITION) {
            $this->expectException('Cache_Exception');
        }

        try {
            $cache = Cache::instance($group);
        } catch (Cache_Exception $e) {
            $this->assertSame($expected, $e->getMessage());
            throw $e;
        }

        $this->assertInstanceOf(get_class($expected), $cache);
        $this->assertSame($expected->config(), $cache->config());
    }

    /**
     * Tests that `clone $cache` will be prevented to maintain singleton
     *
     * @return  void
     */
    public function test_cloning_fails()
    {
        $this->expectException(Cache_Exception::class);
        $this->expectExceptionMessage('Cloning of Kohana_Cache objects is forbidden');

        $cache = $this->getMockBuilder('Cache')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        clone $cache;
    }

    /**
     * Data provider for test_config
     *
     * @return  array
     */
    public function provider_config()
    {
        return [
            [
                [
                    'server' => 'otherhost',
                    'port' => 5555,
                    'persistent' => true,
                ],
                null,
                Kohana_CacheTest::EXPECT_SELF,
                [
                    'server' => 'otherhost',
                    'port' => 5555,
                    'persistent' => true,
                ],
            ],
            [
                'foo',
                'bar',
                Kohana_CacheTest::EXPECT_SELF,
                [
                    'foo' => 'bar'
                ]
            ],
            [
                'server',
                null,
                null,
                []
            ],
            [
                null,
                null,
                [],
                []
            ]
        ];
    }

    /**
     * Tests the config method behaviour
     *
     * @dataProvider provider_config
     *
     * @param mixed $key key value to set or get
     * @param mixed $value value to set to key
     * @param mixed $expected_result expected result from [Cache::config()]
     * @param array $expected_config expected config within cache
     * @return  void
     */
    public function test_config($key, $value, $expected_result, array $expected_config)
    {
        $cache = $this->getMockBuilder('Cache_File')
            ->setMethods(['__construct'])
            ->disableOriginalConstructor()
            ->getMock();

        if ($expected_result === Kohana_CacheTest::EXPECT_SELF) {
            $expected_result = $cache;
        }

        $this->assertSame($expected_result, $cache->config($key, $value));
        $this->assertSame($expected_config, $cache->config());
    }

    /**
     * Data provider for test_sanitize_id
     *
     * @return  array
     */
    public function provider_sanitize_id()
    {
        return [
            [
                'foo',
                'foo'
            ],
            [
                'foo+-!@',
                'foo+-!@'
            ],
            [
                'foo/bar',
                'foo_bar',
            ],
            [
                'foo\\bar',
                'foo_bar'
            ],
            [
                'foo bar',
                'foo_bar'
            ],
            [
                'foo\\bar snafu/stfu',
                'foo_bar_snafu_stfu'
            ]
        ];
    }

    /**
     * Tests the [Cache::_sanitize_id()] method works as expected.
     * This uses some nasty reflection techniques to access a protected
     * method.
     *
     * @dataProvider provider_sanitize_id
     *
     * @param string $id id
     * @param string $expected expected
     * @return  void
     * @throws ReflectionException
     */
    public function test_sanitize_id(string $id, string $expected)
    {
        $cache = $this->createMock('Cache');

        $cache_reflection = new ReflectionClass($cache);
        $sanitize_id = $cache_reflection->getMethod('_sanitize_id');
        $sanitize_id->setAccessible(true);

        $this->assertSame($expected, $sanitize_id->invoke($cache, $id));
    }

}
