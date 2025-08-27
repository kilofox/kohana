<?php

/**
 * Unit tests for external request client
 *
 * @group kohana
 * @group kohana.request
 * @group kohana.request.client
 * @group kohana.request.client.external
 *
 * @package    Kohana
 * @category   Tests
 * @author     Kohana Team
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    https://kohana.top/license
 */
class Kohana_Request_Client_ExternalTest extends Unittest_TestCase
{
    /**
     * Provider for test_factory()
     *
     * @return  array
     */
    public function provider_factory()
    {
        Request_Client_External::$client = 'Request_Client_Stream';

        $return = [
            [[], null, 'Request_Client_Stream'],
            [[], 'Request_Client_Stream', 'Request_Client_Stream'],
        ];

        if (extension_loaded('curl')) {
            $return[] = [[], 'Request_Client_Curl', 'Request_Client_Curl'];
        }

        if (extension_loaded('http')) {
            $return[] = [[], 'Request_Client_HTTP', 'Request_Client_HTTP'];
        }

        return $return;
    }

    /**
     * Tests the [Request_Client_External::factory()] method
     *
     * @dataProvider provider_factory
     *
     * @param array $params params
     * @param string|null $client Client
     * @param string $expected Expected
     * @return  void
     * @throws Request_Exception
     */
    public function test_factory(array $params, $client, $expected)
    {
        $this->assertInstanceOf($expected, Request_Client_External::factory($params, $client));
    }

    /**
     * Data provider for test_options
     *
     * @return  array
     */
    public function provider_options()
    {
        return [
            [
                null,
                null,
                []
            ],
            [
                ['foo' => 'bar', 'stfu' => 'snafu'],
                null,
                ['foo' => 'bar', 'stfu' => 'snafu']
            ],
            [
                'foo',
                'bar',
                ['foo' => 'bar']
            ],
            [
                ['foo' => 'bar'],
                'foo',
                ['foo' => 'bar']
            ],
        ];
    }

    /**
     * Tests the [Request_Client_External::options()] method
     *
     * @dataProvider provider_options
     *
     * @param   mixed  $key  key
     * @param   mixed  $value  value
     * @param array $expected Expected
     * @return  void
     */
    public function test_options($key, $value, array $expected)
    {
        // Create a mock external client
        $client = new Request_Client_Stream;

        $client->options($key, $value);
        $this->assertSame($expected, $client->options());
    }

    /**
     * Data provider for test_execute
     *
     * @return  array
     */
    public function provider_execute()
    {
        $json = '{"foo": "bar", "snafu": "stfu"}';
        $post = ['foo' => 'bar', 'snafu' => 'stfu'];

        return [
            [
                'application/json',
                $json,
                [],
                [
                    'content-type' => 'application/json',
                    'body' => $json
                ]
            ],
            [
                'application/json',
                $json,
                $post,
                [
                    'content-type' => 'application/x-www-form-urlencoded; charset=' . Kohana::$charset,
                    'body' => http_build_query($post, null, '&')
                ]
            ],
        ];
    }

    /**
     * Tests the [Request_Client_External::_send_message()] method
     *
     * @dataProvider provider_execute
     *
     * @param $content_type
     * @param $body
     * @param $post
     * @param $expected
     * @return  void
     * @throws Kohana_Exception
     * @throws Request_Exception
     */
    public function test_execute($content_type, $body, $post, $expected)
    {
        $old_request = Request::$initial;
        Request::$initial = true;

        // Create a mock Request
        $request = new Request('https://kohana.top/');
        $request->method(HTTP_Request::POST)
            ->headers('content-type', $content_type)
            ->body($body)
            ->post($post);

        $client = $this->getMockForAbstractClass('Request_Client_External', [], '', true, true, true, ['_send_message']);
        $client->expects($this->once())
            ->method('_send_message')
            ->with($request)
            ->will($this->returnValue($this->createMock('Response')));

        $request->client($client);

        $this->assertInstanceOf('Response', $request->execute());
        $this->assertSame($expected['body'], $request->body());
        $this->assertSame($expected['content-type'], (string) $request->headers('content-type'));

        Request::$initial = $old_request;
    }

}
