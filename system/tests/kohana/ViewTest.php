<?php

/**
 * Tests the View class
 *
 * @group kohana
 * @group kohana.core
 * @group kohana.core.view
 *
 * @package    Kohana
 * @category   Tests
 * @author     Kohana Team
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    https://kohana.top/license
 */
class Kohana_ViewTest extends Unittest_TestCase
{
    protected static $old_modules = [];

    /**
     * Setups the filesystem for test view files
     *
     * @return void
     * @throws Kohana_Exception
     */
    // @codingStandardsIgnoreStart
    public static function setupBeforeClass()
    // @codingStandardsIgnoreEnd
    {
        self::$old_modules = Kohana::modules();

        $new_modules = self::$old_modules + [
            'test_views' => realpath(dirname(__FILE__) . '/../test_data/')
        ];
        Kohana::modules($new_modules);
    }

    /**
     * Restores the module list
     *
     * @return void
     * @throws Kohana_Exception
     */
    // @codingStandardsIgnoreStart
    public static function teardownAfterClass()
    // @codingStandardsIgnoreEnd
    {
        Kohana::modules(self::$old_modules);
    }

    /**
     * Provider for test_instantiate
     *
     * @return array
     */
    public function provider_instantiate()
    {
        return [
            ['kohana/error', false],
            ['test.css', false],
            ['doesnt_exist', true],
        ];
    }

    /**
     * Provider to test_set
     *
     * @return array
     */
    public function provider_set()
    {
        return [
            ['foo', 'bar', 'foo', 'bar'],
            [['foo' => 'bar'], null, 'foo', 'bar'],
            [new ArrayIterator(['foo' => 'bar']), null, 'foo', 'bar'],
        ];
    }

    /**
     * Tests that we can instantiate a view file
     *
     * @test
     * @dataProvider provider_instantiate
     * @return void
     */
    public function test_instantiate($path, $expects_exception)
    {
        try {
            new View($path);
            $this->assertSame(false, $expects_exception);
        } catch (View_Exception $e) {
            $this->assertSame(true, $expects_exception);
        }
    }

    /**
     * Tests that we can set using string, array or Traversable object
     *
     * @test
     * @dataProvider provider_set
     * @return void
     * @throws View_Exception
     */
    public function test_set($data_key, $value, $test_key, $expected)
    {
        $view = View::factory()->set($data_key, $value);
        $this->assertSame($expected, $view->$test_key);
    }

    /**
     * Tests that we can set global using string, array or Traversable object
     *
     * @test
     * @dataProvider provider_set
     * @return void
     * @throws View_Exception
     */
    public function test_set_global($data_key, $value, $test_key, $expected)
    {
        $view = View::factory();
        $view::set_global($data_key, $value);
        $this->assertSame($expected, $view->$test_key);
    }

}
