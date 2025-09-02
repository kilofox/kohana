<?php

/**
 * Tests Kohana i18n class
 *
 * @group kohana
 * @group kohana.core
 * @group kohana.core.i18n
 *
 * @package    Kohana
 * @category   Tests
 * @author     Kohana Team
 * @author     Jeremy Bush <contractfrombelow@gmail.com>
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    https://kohana.top/license
 */
class Kohana_I18nTest extends Unittest_TestCase
{
    /**
     * Default values for the environment, see setEnvironment
     * @var array
     */
    // @codingStandardsIgnoreStart
    protected $environmentDefault = [
        'I18n::$lang' => 'en-us',
    ];

    // @codingStandardsIgnoreEnd
    /**
     * Provides test data for test_lang()
     *
     * @return array
     */
    public function provider_lang(): array
    {
        return [
            // $input, $expected_result
            [null, 'en-us'],
            ['es-es', 'es-es'],
        ];
    }

    /**
     * Tests I18n::lang()
     *
     * @test
     * @dataProvider provider_lang
     * @param string|null $input Input for I18n::lang
     * @param string $expected Output for I18n::lang
     */
    public function test_lang(?string $input, string $expected)
    {
        $this->assertSame($expected, I18n::lang($input));
        $this->assertSame($expected, I18n::lang());
    }

    /**
     * Provides test data for test_get()
     *
     * @return array
     */
    public function provider_get(): array
    {
        return [
            // $value, $result
            ['en-us', 'Hello, world!', 'Hello, world!'],
            ['es-es', 'Hello, world!', '¡Hola, mundo!'],
            ['fr-fr', 'Hello, world!', 'Bonjour, monde!'],
        ];
    }

    /**
     * Tests i18n::get()
     *
     * @test
     * @dataProvider provider_get
     * @param string $lang Language code to set
     * @param string $input Input for I18n::get
     * @param string $expected Output for I18n::get
     */
    public function test_get(string $lang, string $input, string $expected)
    {
        I18n::lang($lang);
        $this->assertSame($expected, I18n::get($input));

        // Test immediate translation, issue #3085
        I18n::lang('en-us');
        $this->assertSame($expected, I18n::get($input, $lang));
    }

}
