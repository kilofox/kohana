<?php

/**
 * Tests Kohana_Security
 *
 * @group kohana
 * @group kohana.core
 * @group kohana.core.security
 *
 * @package    Kohana
 * @category   Tests
 */
class Kohana_SecurityTest extends Unittest_TestCase
{
    /**
     * Provides test data for test_encode_php_tags()
     *
     * @return array Test data sets
     */
    public function provider_encode_php_tags()
    {
        return [
            ["&lt;?php echo 'helloo'; ?&gt;", "<?php echo 'helloo'; ?>"],
        ];
    }

    /**
     * Tests Security::encode_php_tags()
     *
     * @test
     * @dataProvider provider_encode_php_tags
     * @covers Security::encode_php_tags
     */
    public function test_encode_php_tags($expected, $input)
    {
        $this->assertSame($expected, Security::encode_php_tags($input));
    }

    /**
     * Provides test data for Security::token()
     *
     * @return array Test data sets
     * @throws Kohana_Exception
     */
    public function provider_csrf_token()
    {
        $array = [];
        for ($i = 0; $i <= 4; $i++) {
            Security::$token_name = 'token_' . $i;
            $array[] = [
                Security::token(true),
                Security::check(Security::token()), $i
            ];
        }
        return $array;
    }

    /**
     * Tests Security::token()
     *
     * @test
     * @dataProvider provider_csrf_token
     * @covers Security::token
     */
    public function test_csrf_token($expected, $input, $iteration)
    {
        //@todo: the Security::token tests need to be reviewed to check how much of the logic they're actually covering
        Security::$token_name = 'token_' . $iteration;
        $this->assertSame(true, $input);
        $this->assertSame($expected, Security::token());
        Session::instance()->delete(Security::$token_name);
    }

}
