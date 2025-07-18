<?php

/**
 * Tests the session class
 *
 * @group kohana
 * @group kohana.core
 * @group kohana.core.session
 *
 * @package    Kohana
 * @category   Tests
 * @author     Kohana Team
 * @author     Jeremy Bush <contractfrombelow@gmail.com>
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    https://kohana.top/license
 */
class Kohana_SessionTest extends Unittest_TestCase
{
    /**
     * Gets a mock of the session class
     *
     * @return Session
     */
    // @codingStandardsIgnoreStart
    public function getMockSession(array $config = [])
    // @codingStandardsIgnoreEnd
    {
        return $this->getMockForAbstractClass('Session', [$config]);
    }

    /**
     * Provides test data for
     *
     * test_constructor_uses_name_from_config_and_casts()
     *
     * @return array
     */
    public function provider_constructor_uses_settings_from_config_and_casts()
    {
        return [
            // [expected, input]
            // data set 0
            [
                [
                    'name' => 'awesomeness',
                    'lifetime' => 1231456421,
                    'encrypted' => false
                ],
                [
                    'name' => 'awesomeness',
                    'lifetime' => '1231456421',
                    'encrypted' => false
                ]
            ],
            // data set 1
            [
                [
                    'name' => '123',
                    'encrypted' => 'default'
                ],
                [
                    'name' => 123,
                    'encrypted' => true
                ]
            ],
        ];
    }

    /**
     * The constructor should change its attributes based on config
     * passed as the first parameter
     *
     * @test
     * @dataProvider provider_constructor_uses_settings_from_config_and_casts
     * @covers Session::__construct
     */
    public function test_constructor_uses_settings_from_config_and_casts($expected, $config)
    {
        $session = $this->getMockForAbstractClass('Session', [$config]);

        foreach ($expected as $var => $value) {
            $this->assertAttributeSame($value, '_' . $var, $session);
        }
    }

    /**
     * Check that the constructor will load a session if it's provided
     * with a session id
     *
     * @test
     * @covers Session::__construct
     * @covers Session::read
     */
    public function test_constructor_loads_session_with_session_id()
    {
        $config = [];
        $session_id = 'lolums';

        // Don't auto-call constructor, we need to set up the mock first
        $session = $this->getMockBuilder('Session')
            ->disableOriginalConstructor()
            ->setMethods(['read'])
            ->getMockForAbstractClass();

        $session
            ->expects($this->once())
            ->method('read')
            ->with($session_id);

        $session->__construct($config, $session_id);
    }

    /**
     * Calling $session->bind() should allow you to bind a variable
     * to a session variable
     *
     * @test
     * @covers Session::bind
     * @ticket 3164
     */
    public function test_bind_actually_binds_variable()
    {
        $session = $this->getMockForAbstractClass('Session');

        $var = 'asd';

        $session->bind('our_var', $var);

        $var = 'foobar';

        $this->assertSame('foobar', $session->get('our_var'));
    }

    /**
     * When a session is initially created it should have no data
     *
     *
     * @test
     * @covers Session::__construct
     * @covers Session::set
     */
    public function test_initially_session_has_no_data()
    {
        $session = $this->getMockSession();

        $this->assertAttributeSame([], '_data', $session);
    }

    /**
     * Make sure that the default session name (the one used if the
     * driver does not set one) is 'session'
     *
     * @test
     * @covers Session::__construct
     */
    public function test_default_session_name_is_set()
    {
        $session = $this->getMockSession();

        $this->assertAttributeSame('session', '_name', $session);
    }

    /**
     * By default, sessions are unencrypted
     *
     * @test
     * @covers Session::__construct
     */
    public function test_default_session_is_unencrypted()
    {
        $session = $this->getMockSession();

        $this->assertAttributeSame(false, '_encrypted', $session);
    }

    /**
     * A new session should not be classed as destroyed
     *
     * @test
     * @covers Session::__construct
     */
    public function test_default_session_is_not_classed_as_destroyed()
    {
        $session = $this->getMockSession();

        $this->assertAttributeSame(false, '_destroyed', $session);
    }

    /**
     * Provides test data for test_get_returns_default_if_var_dnx()
     *
     * @return array
     */
    public function provider_get_returns_default_if_var_dnx()
    {
        return [
            ['something_crazy', false],
            ['a_true', true],
            ['an_int', 158163158],
        ];
    }

    /**
     * Make sure that get() is using the default value we provide and
     * isn't tampering with it
     *
     * @test
     * @dataProvider provider_get_returns_default_if_var_dnx
     * @covers Session::get
     */
    public function test_get_returns_default_if_var_dnx($var, $default)
    {
        $session = $this->getMockSession();

        $this->assertSame($default, $session->get($var, $default));
    }

    /**
     * By default, get() should be using null as the var DNX return value
     *
     * @test
     * @covers Session::get
     */
    public function test_get_uses_null_as_default_return_value()
    {
        $session = $this->getMockSession();

        $this->assertSame(null, $session->get('level_of_cool'));
    }

    /**
     * This test makes sure that session is using array_key_exists
     * as isset will return false if the value is null
     *
     * @test
     * @covers Session::get
     */
    public function test_get_returns_value_if_it_equals_null()
    {
        $session = $this->getMockSession();

        $session->set('arkward', null);

        $this->assertSame(null, $session->get('arkward', 'uh oh'));
    }

    /**
     * as_array() should return the session data by reference.
     *
     * i.e. if we modify the returned data, the session data also changes
     *
     * @test
     * @covers Session::as_array
     */
    public function test_as_array_returns_data_by_ref_or_copy()
    {
        $session = $this->getMockSession();

        $data_ref = & $session->as_array();

        $data_ref['something'] = 'pie';

        $this->assertAttributeSame($data_ref, '_data', $session);

        $data_copy = $session->as_array();

        $data_copy['pie'] = 'awesome';

        $this->assertAttributeNotSame($data_copy, '_data', $session);
    }

    /**
     * set() should add new session data and modify existing ones
     *
     * Also makes sure that set() returns $this
     *
     * @test
     * @covers Session::set
     */
    public function test_set_adds_and_modifies_to_session_data()
    {
        $session = $this->getMockSession();

        $this->assertSame($session, $session->set('pork', 'pie'));

        $this->assertAttributeSame(
            ['pork' => 'pie'], '_data', $session
        );

        $session->set('pork', 'delicious');

        $this->assertAttributeSame(
            ['pork' => 'delicious'], '_data', $session
        );
    }

    /**
     * This tests that delete() removes specified session data
     *
     * @test
     * @covers Session::delete
     */
    public function test_delete_removes_select_session_data()
    {
        $session = $this->getMockSession();

        // A bit of a hack for mass-loading session data
        $data = & $session->as_array();

        $data += [
            'a' => 'A',
            'b' => 'B',
            'c' => 'C',
            'easy' => '123'
        ];

        // Make a copy of $data for testing purposes
        $copy = $data;

        // First we make sure we can delete one item
        // Also, check that delete returns $this
        $this->assertSame($session, $session->delete('a'));

        unset($copy['a']);

        // We could test against $data, but then we'd be testing
        // that as_array() is returning by ref
        $this->assertAttributeSame($copy, '_data', $session);

        // Now we make sure we can delete multiple items
        // We're checking $this is returned just in case
        $this->assertSame($session, $session->delete('b', 'c'));
        unset($copy['b'], $copy['c']);

        $this->assertAttributeSame($copy, '_data', $session);
    }

    /**
     * Provides test data for test_read_loads_session_data()
     *
     * @return array
     */
    public function provider_read_loads_session_data()
    {
        return [
            // If driver returns array then just load it up
            [
                [],
                'wacka_wacka',
                []
            ],
            [
                ['the it' => 'crowd'],
                'the_it_crowd',
                ['the it' => 'crowd'],
            ],
            // If it's a string and encryption is disabled (by default), base64_decode and unserialize it
            [
                ['dead' => 'arrival'],
                'lolums',
                'YToxOntzOjQ6ImRlYWQiO3M6NzoiYXJyaXZhbCI7fQ=='
            ],
        ];
    }

    /**
     * This is one of the "big" tests for the session lib
     *
     * The test makes sure that
     *
     * 1. Session asks the driver for the data relating to $session_id
     * 2. That it will load the returned data into the session
     *
     * @test
     * @dataProvider provider_read_loads_session_data
     * @covers Session::read
     */
    public function test_read_loads_session_data($expected_data, $session_id, $driver_data, array $config = [])
    {
        $session = $this->getMockSession($config);

        $session->expects($this->once())
            ->method('_read')
            ->with($session_id)
            ->will($this->returnValue($driver_data));

        $session->read($session_id);
        $this->assertAttributeSame($expected_data, '_data', $session);
    }

    /**
     * regenerate() should tell the driver to regenerate its id
     *
     * @test
     * @covers Session::regenerate
     */
    public function test_regenerate_tells_driver_to_regenerate()
    {
        $session = $this->getMockSession();

        $new_session_id = 'asdnoawdnoainf';

        $session->expects($this->once())
            ->method('_regenerate')
            ->with()
            ->will($this->returnValue($new_session_id));

        $this->assertSame($new_session_id, $session->regenerate());
    }

    /**
     * If the driver destroys the session then all session data should be
     * removed
     *
     * @test
     * @covers Session::destroy
     */
    public function test_destroy_deletes_data_if_driver_destroys_session()
    {
        $session = $this->getMockSession();

        $session
            ->set('asd', 'dsa')
            ->set('dog', 'god');

        $session
            ->expects($this->once())
            ->method('_destroy')
            ->with()
            ->will($this->returnValue(true));

        $this->assertTrue($session->destroy());

        $this->assertAttributeSame([], '_data', $session);
    }

    /**
     * The session data should only be deleted if the driver reports
     * that the session was destroyed ok
     *
     * @test
     * @covers Session::destroy
     */
    public function test_destroy_only_deletes_data_if_driver_destroys_session()
    {
        $session = $this->getMockSession();

        $session
            ->set('asd', 'dsa')
            ->set('dog', 'god');

        $session
            ->expects($this->once())
            ->method('_destroy')
            ->with()
            ->will($this->returnValue(false));

        $this->assertFalse($session->destroy());
        $this->assertAttributeSame(
            ['asd' => 'dsa', 'dog' => 'god'], '_data', $session
        );
    }

    /**
     * If a session variable exists then get_once should get it then remove it.
     * If the variable does not exist then it should return the default
     *
     * @test
     * @covers Session::get_once
     */
    public function test_get_once_gets_once_or_returns_default()
    {
        $session = $this->getMockSession();

        $session->set('foo', 'bar');

        // Test that a default is returned
        $this->assertSame('mud', $session->get_once('fud', 'mud'));

        // Now test that it actually removes the value
        $this->assertSame('bar', $session->get_once('foo'));

        $this->assertAttributeSame([], '_data', $session);

        $this->assertSame('maybe', $session->get_once('foo', 'maybe'));
    }

}
