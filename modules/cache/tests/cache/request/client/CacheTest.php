<?php

/**
 * Unit tests for request client cache logic
 *
 * @group kohana
 * @group kohana.request
 * @group kohana.request.client
 * @group kohana.request.client.cache
 *
 * @package    Kohana
 * @category   Tests
 * @author     Kohana Team
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    https://kohana.top/license
 */
class Kohana_Request_Client_CacheTest extends Unittest_TestCase
{
    /**
     * Sets up a test route for caching
     *
     * @return void
     */
    public function setUp()
    {
        Route::set('welcome', 'welcome/index')
            ->defaults([
                'controller' => 'welcome',
                'action' => 'index'
        ]);

        parent::setUp();
    }

    /**
     * Tests the Client does not attempt to load cache if no Cache library
     * is present
     *
     * @return void
     * @throws Kohana_Exception
     * @throws Request_Exception
     */
    public function test_cache_not_called_with_no_cache()
    {
        $request = new Request('welcome/index');
        $response = new Response;

        $client_mock = $this->createMock('Request_Client_Internal');

        $request->client($client_mock);
        $client_mock->expects($this->exactly(0))
            ->method('execute_request');
        $client_mock->expects($this->once())
            ->method('execute')
            ->will($this->returnValue($response));

        $this->assertSame($response, $request->execute());
    }

    /**
     * Tests that the client attempts to load a cached response from the
     * cache library, but fails.
     *
     * @return void
     * @throws Cache_Exception
     * @throws Kohana_Exception
     * @throws Request_Exception
     */
    public function test_cache_miss()
    {
        $route = new Route('welcome/index');
        $route->defaults([
            'controller' => 'Kohana_Request_CacheTest_Dummy',
            'action' => 'index',
        ]);

        $request = new Request('welcome/index', null, [$route]);
        $cache_mock = $this->createMock('Cache_File');

        $request->client()->cache(HTTP_Cache::factory($cache_mock));

        $cache_mock->expects($this->once())
            ->method('get')
            ->with($request->client()->cache()->create_cache_key($request))
            ->will($this->returnValue(false));

        $response = $request->client()->execute($request);

        $this->assertSame(HTTP_Cache::CACHE_STATUS_MISS, $response->headers(HTTP_Cache::CACHE_STATUS_KEY));
    }

    /**
     * Tests the client saves a response if the correct headers are set
     *
     * @return void
     * @throws Cache_Exception
     * @throws Kohana_Exception
     */
    public function test_cache_save()
    {
        $lifetime = 800;
        $request = new Request('welcome/index');
        $cache_mock = $this->createMock('Cache_File');
        $response = Response::factory();

        $request->client()->cache(new HTTP_Cache(['cache' => $cache_mock]));

        $response->headers('cache-control', 'max-age=' . $lifetime);

        $key = $request->client()->cache()->create_cache_key($request);

        $cache_mock->expects($this->at(0))
            ->method('set')
            ->with($this->stringEndsWith($key), $this->identicalTo(0));

        $cache_mock->expects($this->at(1))
            ->method('set')
            ->with($this->identicalTo($key), $this->anything(), $this->identicalTo($lifetime))
            ->will($this->returnValue(true));

        $this->assertTrue(
            $request->client()->cache()
                ->cache_response($key, $request, $response)
        );

        $this->assertSame(HTTP_Cache::CACHE_STATUS_SAVED, $response->headers(HTTP_Cache::CACHE_STATUS_KEY));
    }

    /**
     * Tests the client handles a cache HIT event correctly
     *
     * @return void
     * @throws Cache_Exception
     * @throws Kohana_Exception
     */
    public function test_cache_hit()
    {
        $lifetime = 800;
        $request = new Request('welcome/index');
        $cache_mock = $this->createMock('Cache_File');

        $request->client()->cache(new HTTP_Cache(['cache' => $cache_mock]));

        $response = Response::factory();

        $response->headers([
            'cache-control' => 'max-age=' . $lifetime,
            HTTP_Cache::CACHE_STATUS_KEY =>
            HTTP_Cache::CACHE_STATUS_HIT
        ]);

        $key = $request->client()->cache()->create_cache_key($request);

        $cache_mock->expects($this->exactly(2))
            ->method('get')
            ->with($this->stringContains($key))
            ->will($this->returnValue($response));

        $request->client()->cache()->cache_response($key, $request);

        $this->assertSame(HTTP_Cache::CACHE_STATUS_HIT, $response->headers(HTTP_Cache::CACHE_STATUS_KEY));
    }

    /**
     * Data provider for test_set_cache
     *
     * @return array
     */
    public function provider_set_cache()
    {
        return [
            [
                new HTTP_Header(['cache-control' => 'no-cache']),
                ['no-cache' => null],
                false,
            ],
            [
                new HTTP_Header(['cache-control' => 'no-store']),
                ['no-store' => null],
                false,
            ],
            [
                new HTTP_Header(['cache-control' => 'max-age=100']),
                ['max-age' => '100'],
                true
            ],
            [
                new HTTP_Header(['cache-control' => 'private']),
                ['private' => null],
                false
            ],
            [
                new HTTP_Header(['cache-control' => 'private, max-age=100']),
                ['private' => null, 'max-age' => '100'],
                false
            ],
            [
                new HTTP_Header(['cache-control' => 'private, s-maxage=100']),
                ['private' => null, 's-maxage' => '100'],
                true
            ],
            [
                new HTTP_Header(['expires' => date('m/d/Y', strtotime('-1 day'))]),
                [],
                false
            ],
            [
                new HTTP_Header(['expires' => date('m/d/Y', strtotime('+1 day'))]),
                [],
                true
            ],
            [
                new HTTP_Header([]),
                [],
                true
            ],
        ];
    }

    /**
     * Tests the set_cache() method
     *
     * @test
     * @dataProvider provider_set_cache
     * @return void
     */
    public function test_set_cache($headers, $cache_control, $expected)
    {
        /**
         * Set up a mock response object to test with
         */
        $response = $this->createMock('Response');

        $response->expects($this->any())
            ->method('headers')
            ->will($this->returnValue($headers));

        $request = new Request_Client_Internal;
        $request->cache(new HTTP_Cache);
        $this->assertEquals($request->cache()->set_cache($response), $expected);
    }

}

class Controller_Kohana_Request_CacheTest_Dummy extends Controller
{
    public function action_index()
    {

    }

}
