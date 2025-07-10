<?php

/**
 * Unit tests for internal request client
 *
 * @group kohana
 * @group kohana.core
 * @group kohana.core.request
 * @group kohana.core.request.client
 * @group kohana.core.request.client.internal
 *
 * @package    Kohana
 * @category   Tests
 * @author     Kohana Team
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    https://kohana.top/license
 */
class Kohana_Request_Client_InternalTest extends Unittest_TestCase
{
    protected $_log_object;

    // @codingStandardsIgnoreStart
    public function setUp()
    // @codingStandardsIgnoreEnd
    {
        parent::setUp();

        // temporarily save $log object
        $this->_log_object = Kohana::$log;
        Kohana::$log = null;
    }

    // @codingStandardsIgnoreStart
    public function tearDown()
    // @codingStandardsIgnoreEnd
    {
        // re-assign log object
        Kohana::$log = $this->_log_object;

        parent::tearDown();
    }

    public function provider_response_failure_status()
    {
        return [
            [
                '',
                'Welcome',
                'missing_action',
                'Welcome/missing_action',
                404
            ],
            [
                'kohana3',
                'missing_controller',
                'index',
                'kohana3/missing_controller/index',
                404
            ],
            [
                '',
                'Template',
                'missing_action',
                'kohana3/Template/missing_action',
                500
            ],
        ];
    }

    /**
     * Tests for correct exception messages
     *
     * @test
     * @dataProvider provider_response_failure_status
     *
     * @return void
     * @throws Kohana_Exception
     */
    public function test_response_failure_status($directory, $controller, $action, $uri, $expected)
    {
        // Mock for request object
        $request = $this->getMockBuilder('Request')
            ->setMethods(['directory', 'controller', 'action', 'uri', 'execute', 'method'])
            ->setConstructorArgs([$uri])
            ->getMock();

        $request->expects($this->any())
            ->method('directory')
            ->will($this->returnValue($directory));

        $request->expects($this->any())
            ->method('controller')
            ->will($this->returnValue($controller));

        $request->expects($this->any())
            ->method('action')
            ->will($this->returnValue($action));

        $request->expects($this->any())
            ->method('uri')
            ->will($this->returnValue($uri));

        $request->expects($this->any())
            ->method('execute')
            ->will($this->returnValue($this->createMock('Response')));

        // mock `method` method to avoid fatal errors in newer versions of PHPUnit
        $request->expects($this->any())
            ->method('method')
            ->withAnyParameters();

        $internal_client = new Request_Client_Internal;

        $response = $internal_client->execute($request);

        $this->assertSame($expected, $response->status());
    }

}
