<?php

/**
 * Tests Kohana inflector class
 *
 * @group kohana
 * @group kohana.core
 * @group kohana.core.inflector
 *
 * @package    Kohana
 * @category   Tests
 * @author     Kohana Team
 * @author     Jeremy Bush <contractfrombelow@gmail.com>
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    https://kohana.top/license
 */
class Kohana_InflectorTest extends Unittest_TestCase
{
    /**
     * Provides test data for test_lang()
     *
     * @return array
     */
    public function provider_uncountable(): array
    {
        return [
            // $value, $result
            ['fish', true],
            ['cat', false],
            ['deer', true],
            ['bison', true],
            ['friend', false],
        ];
    }

    /**
     * Tests Inflector::uncountable
     *
     * @test
     * @dataProvider provider_uncountable
     * @param string $input Input for Inflector::uncountable
     * @param bool $expected Output for Inflector::uncountable
     * @throws Kohana_Exception
     */
    public function test_uncountable(string $input, bool $expected)
    {
        $this->assertSame($expected, Inflector::uncountable($input));
    }

    /**
     * Provides test data for test_lang()
     *
     * @return array
     */
    public function provider_singular(): array
    {
        return [
            // $value, $result
            ['fish', null, 'fish'],
            ['cats', null, 'cat'],
            ['cats', 2, 'cats'],
            ['cats', '2', 'cats'],
            ['children', null, 'child'],
            ['meters', 0.6, 'meters'],
            ['meters', 1.6, 'meters'],
            ['meters', 1.0, 'meter'],
            ['status', null, 'status'],
            ['statuses', null, 'status'],
            ['heroes', null, 'hero'],
        ];
    }

    /**
     * Tests Inflector::singular
     *
     * @test
     * @dataProvider provider_singular
     * @param string $input Input for Inflector::singular
     * @param int|float $count Optional count to determine inflection logic
     * @param string $expected Output for Inflector::mime
     * @throws Kohana_Exception
     */
    public function test_singular(string $input, $count, string $expected)
    {
        $this->assertSame($expected, Inflector::singular($input, $count));
    }

    /**
     * Provides test data for test_lang()
     *
     * @return array
     */
    public function provider_plural(): array
    {
        return [
            // $value, $result
            ['fish', null, 'fish'],
            ['cat', null, 'cats'],
            ['cats', 1, 'cats'],
            ['cats', '1', 'cats'],
            ['movie', null, 'movies'],
            ['meter', 0.6, 'meters'],
            ['meter', 1.6, 'meters'],
            ['meter', 1.0, 'meter'],
            ['hero', null, 'heroes'],
            ['Dog', null, 'Dogs'], // Titlecase
            ['DOG', null, 'DOGS'], // Uppercase
        ];
    }

    /**
     * Tests Inflector::plural
     *
     * @test
     * @dataProvider provider_plural
     * @param string $input Input for Inflector::plural
     * @param int|float $count Optional count to determine inflection logic
     * @param string $expected Output for Inflector::plural
     * @throws Kohana_Exception
     */
    public function test_plural(string $input, $count, string $expected)
    {
        $this->assertSame($expected, Inflector::plural($input, $count));
    }

    /**
     * Provides test data for test_camelize()
     *
     * @return array
     */
    public function provider_camelize(): array
    {
        return [
            // $value, $result
            ['mother cat', 'camelize', 'motherCat'],
            ['kittens in bed', 'camelize', 'kittensInBed'],
            ['mother cat', 'underscore', 'mother_cat'],
            ['kittens in bed', 'underscore', 'kittens_in_bed'],
            ['kittens-are-cats', 'humanize', 'kittens are cats'],
            ['dogs_as_well', 'humanize', 'dogs as well'],
        ];
    }

    /**
     * Tests Inflector methods like camelize, underscore, and humanize.
     *
     * @test
     * @dataProvider provider_camelize
     * @param string $input The input string to transform
     * @param string $method The Inflector method to test
     * @param string $expected The expected transformed string
     */
    public function test_camelize(string $input, string $method, string $expected)
    {
        $this->assertSame($expected, Inflector::$method($input));
    }

    /**
     * Provides data for test_decamelize()
     *
     * @return array
     */
    public function provider_decamelize(): array
    {
        return [
            ['getText', '_', 'get_text'],
            ['getJSON', '_', 'get_json'],
            ['getLongText', '_', 'get_long_text'],
            ['getI18N', '_', 'get_i18n'],
            ['getL10n', '_', 'get_l10n'],
            ['getTe5t1ng', '_', 'get_te5t1ng'],
            ['OpenFile', '_', 'open_file'],
            ['CloseIoSocket', '_', 'close_io_socket'],
            ['fooBar', ' ', 'foo bar'],
            ['camelCase', '+', 'camel+case'],
        ];
    }

    /**
     * Tests Inflector::decamelize()
     *
     * @test
     * @dataProvider provider_decamelize
     * @param string $input Camelized string
     * @param string $glue Glue
     * @param string $expected Expected string
     */
    public function test_decamelize(string $input, string $glue, string $expected)
    {
        $this->assertSame($expected, Inflector::decamelize($input, $glue));
    }

}
