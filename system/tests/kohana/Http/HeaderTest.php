<?php

/**
 * Unit Tests for Kohana_HTTP_Header
 *
 * @group kohana
 * @group kohana.core
 * @group kohana.core.http
 * @group kohana.core.http.header
 * @group kohana.core.http.header
 *
 * @package    Kohana
 * @category   Tests
 * @author     Kohana Team
 * @copyright  (c) 2008-2014 Kohana Team
 * @license    https://kohana.top/license
 */
class Kohana_HTTP_HeaderTest extends Unittest_TestCase
{
    /**
     * Provides data for test_accept_quality
     *
     * @return  array
     */
    public function provider_accept_quality()
    {
        return [
            [
                [
                    'text/html; q=1',
                    'text/plain; q=.5',
                    'application/json; q=.1',
                    'text/*'
                ],
                [
                    'text/html' => 1.0,
                    'text/plain' => 0.5,
                    'application/json' => 0.1,
                    'text/*' => 1.0
                ]
            ],
            [
                [
                    'text/*',
                    'text/html; level=1; q=0.4',
                    'application/xml+rss; q=0.5; level=4'
                ],
                [
                    'text/*' => 1.0,
                    'text/html; level=1' => 0.4,
                    'application/xml+rss; level=4' => 0.5
                ]
            ],
        ];
    }

    /**
     * Tests the `accept_quality` method parses the quality values
     * correctly out of header parts
     *
     * @dataProvider provider_accept_quality
     *
     * @param   array  $parts     input
     * @param   array  $expected  expected output
     * @return  void
     */
    public function test_accept_quality(array $parts, array $expected)
    {
        $out = HTTP_Header::accept_quality($parts);

        foreach ($out as $value) {
            $this->assertInternalType('float', $value);
        }

        $this->assertSame($expected, $out);
    }

    /**
     * Data provider for test_parse_accept_header
     *
     * @return  array
     */
    public function provider_parse_accept_header()
    {
        return [
            [
                'text/html, text/plain, text/*, */*',
                [
                    'text' => [
                        'html' => 1.0,
                        'plain' => 1.0,
                        '*' => 1.0
                    ],
                    '*' => [
                        '*' => 1.0
                    ]
                ]
            ],
            [
                'text/html; q=.5, application/json, application/xml+rss; level=1; q=.7, text/*, */*',
                [
                    'text' => [
                        'html' => 0.5,
                        '*' => 1.0
                    ],
                    'application' => [
                        'json' => 1.0,
                        'xml+rss; level=1' => 0.7
                    ],
                    '*' => [
                        '*' => 1.0
                    ]
                ]
            ],
        ];
    }

    /**
     * Tests the `parse_accept_header` method parses the Accept: header
     * correctly and returns expected output
     *
     * @dataProvider provider_parse_accept_header
     *
     * @param   string  $accept    accept in
     * @param   array   $expected  expected out
     * @return  void
     */
    public function test_parse_accept_header($accept, array $expected)
    {
        $this->assertSame($expected, HTTP_Header::parse_accept_header($accept));
    }

    /**
     * Provides data for test_parse_charset_header
     *
     * @return  array
     */
    public function provider_parse_charset_header()
    {
        return [
            [
                'utf-8, utf-10, utf-16, iso-8859-1',
                [
                    'utf-8' => 1.0,
                    'utf-10' => 1.0,
                    'utf-16' => 1.0,
                    'iso-8859-1' => 1.0
                ]
            ],
            [
                'utf-8, utf-10; q=.9, utf-16; q=.5, iso-8859-1; q=.75',
                [
                    'utf-8' => 1.0,
                    'utf-10' => 0.9,
                    'utf-16' => 0.5,
                    'iso-8859-1' => 0.75
                ]
            ],
            [
                null,
                [
                    '*' => 1.0
                ]
            ],
        ];
    }

    /**
     * Tests the `parse_charset_header` method parsed the Accept-Charset header
     * correctly
     *
     * @dataProvider provider_parse_charset_header
     *
     * @param   string  $accept    accept
     * @param   array   $expected  expected
     * @return  void
     */
    public function test_parse_charset_header($accept, array $expected)
    {
        $this->assertSame($expected, HTTP_Header::parse_charset_header($accept));
    }

    /**
     * Provides data for test_parse_charset_header
     *
     * @return  array
     */
    public function provider_parse_encoding_header()
    {
        return [
            [
                'compress, gzip, blowfish',
                [
                    'compress' => 1.0,
                    'gzip' => 1.0,
                    'blowfish' => 1.0
                ]
            ],
            [
                'compress, gzip; q=0.12345, blowfish; q=1.0',
                [
                    'compress' => 1.0,
                    'gzip' => 0.12345,
                    'blowfish' => 1.0
                ]
            ],
            [
                null,
                [
                    '*' => 1.0
                ]
            ],
            [
                '',
                [
                    'identity' => 1.0
                ]
            ],
        ];
    }

    /**
     * Tests the `parse_encoding_header` method parses the Accept-Encoding header
     * correctly
     *
     * @dataProvider provider_parse_encoding_header
     *
     * @param   string  $accept    accept
     * @param   array   $expected  expected
     * @return  void
     */
    public function test_parse_encoding_header($accept, array $expected)
    {
        $this->assertSame($expected, HTTP_Header::parse_encoding_header($accept));
    }

    /**
     * Provides data for test_parse_charset_header
     *
     * @return  array
     */
    public function provider_parse_language_header()
    {
        return [
            [
                'en, en-us, en-gb, fr, fr-fr, es-es',
                [
                    'en' => [
                        '*' => 1.0,
                        'us' => 1.0,
                        'gb' => 1.0
                    ],
                    'fr' => [
                        '*' => 1.0,
                        'fr' => 1.0
                    ],
                    'es' => [
                        'es' => 1.0
                    ]
                ]
            ],
            [
                'en; q=.9, en-us, en-gb, fr; q=.5, fr-fr; q=0.4, es-es; q=0.9, en-gb-gb; q=.45',
                [
                    'en' => [
                        '*' => 0.9,
                        'us' => 1.0,
                        'gb' => 1.0,
                        'gb-gb' => 0.45
                    ],
                    'fr' => [
                        '*' => 0.5,
                        'fr' => 0.4
                    ],
                    'es' => [
                        'es' => 0.9
                    ]
                ]
            ],
            [
                null,
                [
                    '*' => [
                        '*' => 1.0
                    ]
                ]
            ],
        ];
    }

    /**
     * Tests the `parse_language_header` method parses the Accept-Language header
     * correctly
     *
     * @dataProvider provider_parse_language_header
     *
     * @param   string  $accept    accept
     * @param   array   $expected  expected
     * @return  void
     */
    public function test_parse_language_header($accept, array $expected)
    {
        $this->assertSame($expected, HTTP_Header::parse_language_header($accept));
    }

    /**
     * Data provider for test_create_cache_control
     *
     * @return  array
     */
    public function provider_create_cache_control()
    {
        return [
            [
                [
                    'public',
                    'max-age' => 1800,
                    'must-revalidate',
                    's-max-age' => 3600
                ],
                'public, max-age=1800, must-revalidate, s-max-age=3600'
            ],
            [
                [
                    'max-age' => 1800,
                    's-max-age' => 1800,
                    'public',
                    'must-revalidate',
                ],
                'max-age=1800, s-max-age=1800, public, must-revalidate'
            ],
            [
                [
                    'private',
                    'no-cache',
                    'max-age' => 0,
                    'must-revalidate'
                ],
                'private, no-cache, max-age=0, must-revalidate'
            ],
        ];
    }

    /**
     * Tests that `create_cache_control()` outputs the correct cache control
     * string from the supplied input
     *
     * @dataProvider provider_create_cache_control
     *
     * @param   array   $input     input
     * @param   string  $expected  expected
     * @return  void
     */
    public function test_create_cache_control(array $input, $expected)
    {
        $this->assertSame($expected, HTTP_Header::create_cache_control($input));
    }

    /**
     * Data provider for parse_cache_control
     *
     * @return  array
     */
    public function provider_parse_cache_control()
    {
        return [
            [
                'public, max-age=1800, must-revalidate, s-max-age=3600',
                [
                    'public',
                    'max-age' => 1800,
                    'must-revalidate',
                    's-max-age' => 3600
                ]
            ],
            [
                'max-age=1800, s-max-age=1800, public, must-revalidate',
                [
                    'max-age' => 1800,
                    's-max-age' => 1800,
                    'public',
                    'must-revalidate',
                ]
            ],
            [
                'private, no-cache, max-age=0, must-revalidate',
                [
                    'private',
                    'no-cache',
                    'max-age' => 0,
                    'must-revalidate'
                ]
            ],
        ];
    }

    /**
     * Tests that `parse_cache_control()` outputs the correct cache control
     * parsed data from the input string
     *
     * @dataProvider provider_parse_cache_control
     *
     * @param   string  $input     input
     * @param   array   $expected  expected
     * @return  void
     */
    public function test_parse_cache_control($input, array $expected)
    {
        $parsed = HTTP_Header::parse_cache_control($input);

        $this->assertInternalType('array', $parsed);

        foreach ($expected as $key => $value) {
            if (is_int($key)) {
                $this->assertTrue(in_array($value, $parsed));
            } else {
                $this->assertTrue(array_key_exists($key, $parsed));
                $this->assertSame($value, $parsed[$key]);
            }
        }
    }

    /**
     * Data provider for test_offsetSet
     *
     * @return  array
     */
    // @codingStandardsIgnoreStart
    public function provider_offsetSet()
    // @codingStandardsIgnoreEnd
    {
        return [
            [
                [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Accept' => 'text/html, text/plain; q=.1, */*',
                    'Accept-Language' => 'en-gb, en-us, en; q=.1'
                ],
                [
                    ['Accept-Encoding', 'compress, gzip', false]
                ],
                [
                    'content-type' => 'application/x-www-form-urlencoded',
                    'accept' => 'text/html, text/plain; q=.1, */*',
                    'accept-language' => 'en-gb, en-us, en; q=.1',
                    'accept-encoding' => 'compress, gzip'
                ]
            ],
            [
                [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Accept' => 'text/html, text/plain; q=.1, */*',
                    'Accept-Language' => 'en-gb, en-us, en; q=.1'
                ],
                [
                    ['Accept-Encoding', 'compress, gzip', false],
                    ['Accept-Encoding', 'bzip', false]
                ],
                [
                    'content-type' => 'application/x-www-form-urlencoded',
                    'accept' => 'text/html, text/plain; q=.1, */*',
                    'accept-language' => 'en-gb, en-us, en; q=.1',
                    'accept-encoding' => ['compress, gzip', 'bzip']
                ]
            ],
            [
                [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Accept' => 'text/html, text/plain; q=.1, */*',
                    'Accept-Language' => 'en-gb, en-us, en; q=.1'
                ],
                [
                    ['Accept-Encoding', 'compress, gzip', false],
                    ['Accept-Encoding', 'bzip', true],
                    ['Accept', 'text/*', false]
                ],
                [
                    'content-type' => 'application/x-www-form-urlencoded',
                    'accept' => ['text/html, text/plain; q=.1, */*', 'text/*'],
                    'accept-language' => 'en-gb, en-us, en; q=.1',
                    'accept-encoding' => 'bzip'
                ]
            ],
        ];
    }

    /**
     * Ensures that offsetSet normalizes the array keys
     *
     * @dataProvider provider_offsetSet
     *
     * @param array $constructor constructor
     * @param array $to_set to_set
     * @param array $expected expected
     * @return  void
     * @throws ReflectionException
     */
    // @codingStandardsIgnoreStart
    public function test_offsetSet(array $constructor, array $to_set, array $expected)
    // @codingStandardsIgnoreEnd
    {
        $http_header = new HTTP_Header($constructor);

        $reflection = new ReflectionClass($http_header);
        $method = $reflection->getMethod('offsetSet');

        foreach ($to_set as $args) {
            $method->invokeArgs($http_header, $args);
        }

        $this->assertSame($expected, $http_header->getArrayCopy());
    }

    /**
     * Data provider for test_offsetGet
     *
     * @return  array
     */
    // @codingStandardsIgnoreStart
    public function provider_offsetGet()
    // @codingStandardsIgnoreEnd
    {
        return [
            [
                [
                    'FoO' => 'bar',
                    'START' => 'end',
                    'true' => true
                ],
                'FOO',
                'bar'
            ],
            [
                [
                    'FoO' => 'bar',
                    'START' => 'end',
                    'true' => true
                ],
                'true',
                true
            ],
            [
                [
                    'FoO' => 'bar',
                    'START' => 'end',
                    'true' => true
                ],
                'True',
                true
            ],
            [
                [
                    'FoO' => 'bar',
                    'START' => 'end',
                    'true' => true
                ],
                'Start',
                'end'
            ],
            [
                [
                    'content-type' => 'bar',
                    'Content-Type' => 'end',
                    'Accept' => '*/*'
                ],
                'content-type',
                'end'
            ],
        ];
    }

    /**
     * Ensures that offsetGet normalizes the array keys
     *
     * @dataProvider provider_offsetGet
     *
     * @param   array     start state
     * @param   string    key to retrieve
     * @param   mixed     expected
     * @return  void
     */
    // @codingStandardsIgnoreStart
    public function test_offsetGet(array $state, $key, $expected)
    // @codingStandardsIgnoreEnd
    {
        $header = new HTTP_Header($state);

        $this->assertSame($expected, $header->offsetGet($key));
    }

    /**
     * Data provider for test_offsetExists
     *
     * @return  array
     */
    // @codingStandardsIgnoreStart
    public function provider_offsetExists()
    // @codingStandardsIgnoreEnd
    {
        return [
            [
                [
                    'Accept' => 'text/html, application/json',
                    'Accept-Language' => 'en, en-GB',
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ],
                'Content-Type',
                true
            ],
            [
                [
                    'Accept' => 'text/html, application/json',
                    'Accept-Language' => 'en, en-GB',
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ],
                'CONTENT-TYPE',
                true
            ],
            [
                [
                    'Accept' => 'text/html, application/json',
                    'Accept-Language' => 'en, en-GB',
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ],
                'accept-language',
                true
            ],
            [
                [
                    'Accept' => 'text/html, application/json',
                    'Accept-Language' => 'en, en-GB',
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ],
                'x-powered-by',
                false
            ],
        ];
    }

    /**
     * Ensures that offsetExists normalizes the array key
     *
     * @dataProvider provider_offsetExists
     *
     * @param   array    $state     state
     * @param   string   $key       key
     * @param   boolean  $expected  expected
     * @return  void
     */
    // @codingStandardsIgnoreStart
    public function test_offsetExists(array $state, $key, $expected)
    // @codingStandardsIgnoreEnd
    {
        $header = new HTTP_Header($state);

        $this->assertSame($expected, $header->offsetExists($key));
    }

    /**
     * Data provider for test_offsetUnset
     *
     * @return  array
     */
    // @codingStandardsIgnoreStart
    public function provider_offsetUnset()
    // @codingStandardsIgnoreEnd
    {
        return [
            [
                [
                    'Accept' => 'text/html, application/json',
                    'Accept-Language' => 'en, en-GB',
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ],
                'Accept-Language',
                [
                    'accept' => 'text/html, application/json',
                    'content-type' => 'application/x-www-form-urlencoded'
                ]
            ],
            [
                [
                    'Accept' => 'text/html, application/json',
                    'Accept-Language' => 'en, en-GB',
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ],
                'ACCEPT',
                [
                    'accept-language' => 'en, en-GB',
                    'content-type' => 'application/x-www-form-urlencoded'
                ]
            ],
            [
                [
                    'Accept' => 'text/html, application/json',
                    'Accept-Language' => 'en, en-GB',
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ],
                'content-type',
                [
                    'accept' => 'text/html, application/json',
                    'accept-language' => 'en, en-GB',
                ]
            ],
        ];
    }

    /**
     * Tests that `offsetUnset` normalizes the key names properly
     *
     * @dataProvider provider_offsetUnset
     *
     * @param   array   $state     state
     * @param   string  $remove    remove
     * @param   array   $expected  expected
     * @return  void
     */
    // @codingStandardsIgnoreStart
    public function test_offsetUnset(array $state, $remove, array $expected)
    // @codingStandardsIgnoreEnd
    {
        $header = new HTTP_Header($state);
        $header->offsetUnset($remove);

        $this->assertSame($expected, $header->getArrayCopy());
    }

    /**
     * Provides data for test_parse_header_string
     *
     * @return  array
     */
    public function provider_parse_header_string()
    {
        return [
            [
                [
                    "Content-Type: application/x-www-form-urlencoded\r\n",
                    "Accept: text/html, text/plain; q=.5, application/json, */* \r\n",
                    "X-Powered-By: Kohana Baby     \r\n"
                ],
                [
                    'content-type' => 'application/x-www-form-urlencoded',
                    'accept' => 'text/html, text/plain; q=.5, application/json, */* ',
                    'x-powered-by' => 'Kohana Baby     '
                ]
            ],
            [
                [
                    "Content-Type: application/x-www-form-urlencoded\r\n",
                    "Accept: text/html, text/plain; q=.5, application/json, */* \r\n",
                    "X-Powered-By: Kohana Baby     \r\n",
                    "Content-Type: application/json\r\n"
                ],
                [
                    'content-type' => [
                        'application/x-www-form-urlencoded',
                        'application/json'
                    ],
                    'accept' => 'text/html, text/plain; q=.5, application/json, */* ',
                    'x-powered-by' => 'Kohana Baby     '
                ]
            ],
        ];
    }

    /**
     * Tests that `parse_header_string` performs as expected
     *
     * @dataProvider provider_parse_header_string
     *
     * @param   array    headers
     * @param   array    expected
     * @return  void
     */
    public function test_parse_header_string(array $headers, array $expected)
    {
        $http_header = new HTTP_Header([]);

        foreach ($headers as $header) {

            $this->assertEquals(strlen($header), $http_header->parse_header_string(null, $header));
        }

        $this->assertSame($expected, $http_header->getArrayCopy());
    }

    /**
     * Data Provider for test_accepts_at_quality
     *
     * @return  array
     */
    public function provider_accepts_at_quality()
    {
        return [
            [
                [
                    'Accept' => 'application/json, text/html; q=.5, text/*; q=.1, */*'
                ],
                'application/json',
                false,
                1.0
            ],
            [
                [
                    'Accept' => 'application/json, text/html; q=.5, text/*; q=.1, */*'
                ],
                'text/html',
                false,
                0.5
            ],
            [
                [
                    'Accept' => 'application/json, text/html; q=.5, text/*; q=.1, */*'
                ],
                'text/plain',
                false,
                0.1
            ],
            [
                [
                    'Accept' => 'application/json, text/html; q=.5, text/*; q=.1, */*'
                ],
                'text/plain',
                true,
                false
            ],
            [
                [
                    'Accept' => 'application/json, text/html; q=.5, text/*; q=.1, */*'
                ],
                'application/xml',
                false,
                1.0
            ],
            [
                [
                    'Accept' => 'application/json, text/html; q=.5, text/*; q=.1, */*'
                ],
                'application/xml',
                true,
                false
            ],
            [
                [],
                'application/xml',
                false,
                1.0
            ],
            [
                [],
                'application/xml',
                true,
                false
            ],
        ];
    }

    /**
     * Tests `accepts_at_quality` parsed the Accept: header as expected
     *
     * @dataProvider provider_accepts_at_quality
     *
     * @param array     starting state
     * @param string    accept header to test
     * @param boolean   explicitly check
     * @param mixed     expected output
     * @return  void
     * @throws Kohana_Exception
     */
    public function test_accepts_at_quality(array $state, $accept, $explicit, $expected)
    {
        $header = new HTTP_Header($state);

        $this->assertSame($expected, $header->accepts_at_quality($accept, $explicit));
    }

    /**
     * Data provider for test_preferred_accept
     *
     * @return  array
     */
    public function provider_preferred_accept()
    {
        return [
            [
                [
                    'Accept' => 'application/json, text/html; q=.5, text/*; q=.1, */*'
                ],
                ['text/html', 'application/json', 'text/plain'],
                false,
                'application/json'
            ],
            [
                [
                    'Accept' => 'application/json, text/html; q=.5, text/*; q=.1, */*'
                ],
                ['text/plain', 'application/xml', 'image/jpeg'],
                false,
                'application/xml'
            ],
            [
                [
                    'Accept' => 'application/json, text/html; q=.5, text/*; q=.1'
                ],
                ['text/plain', 'application/xml', 'image/jpeg'],
                false,
                'text/plain'
            ],
            [
                [
                    'Accept' => 'application/json, text/html; q=.5, text/*; q=.1, */*'
                ],
                ['text/plain', 'application/xml', 'image/jpeg'],
                true,
                false
            ],
        ];
    }

    /**
     * Tests `preferred_accept` returns the correct preferred type
     *
     * @dataProvider provider_preferred_accept
     *
     * @param array     state
     * @param array     accepts
     * @param string    explicit
     * @param string    expected
     * @return  void
     * @throws Kohana_Exception
     */
    public function test_preferred_accept(array $state, array $accepts, $explicit, $expected)
    {
        $header = new HTTP_Header($state);

        $this->assertSame($expected, $header->preferred_accept($accepts, $explicit));
    }

    /**
     * Data provider for test_accepts_charset_at_quality
     *
     * @return  array
     */
    public function provider_accepts_charset_at_quality()
    {
        return [
            [
                [
                    'Accept-Charset' => 'utf-8, utf-10, utf-16, iso-8859-1'
                ],
                'utf-8',
                1.0
            ],
            [
                [
                    'Accept-Charset' => 'utf-8, utf-10, utf-16, iso-8859-1'
                ],
                'utf-16',
                1.0
            ],
            [
                [
                    'Accept-Charset' => 'utf-8; q=.1, utf-10, utf-16; q=.2, iso-8859-1'
                ],
                'utf-8',
                0.1
            ],
            [
                [
                    'Accept-Charset' => 'utf-8; q=.1, utf-10, utf-16; q=.2, iso-8859-1; q=.5'
                ],
                'iso-8859-1',
                0.5
            ],
        ];
    }

    /**
     * Tests `accepts_charset_at_quality` works as expected, returning the correct
     * quality value
     *
     * @dataProvider provider_accepts_charset_at_quality
     *
     * @param   array     state
     * @param   string    charset
     * @param   string    expected
     * @return  void
     */
    public function test_accepts_charset_at_quality(array $state, $charset, $expected)
    {
        $header = new HTTP_Header($state);

        $this->assertSame($expected, $header->accepts_charset_at_quality($charset));
    }

    /**
     * Data provider for test_preferred_charset
     *
     * @return  array
     */
    public function provider_preferred_charset()
    {
        return [
            [
                ['Accept-Charset' => 'utf-8, utf-10, utf-16, iso-8859-1'],
                ['utf-8', 'utf-16'],
                'utf-8'
            ],
            [
                ['Accept-Charset' => 'utf-8, utf-10, utf-16, iso-8859-1'],
                ['UTF-10'],
                'UTF-10'
            ],
        ];
    }

    /**
     * Tests `preferred_charset` works as expected, returning the correct charset
     * from the list supplied
     *
     * @dataProvider provider_preferred_charset
     *
     * @param   array     state
     * @param   array     charsets
     * @param   string    expected
     * @return  void
     */
    public function test_preferred_charset(array $state, array $charsets, $expected)
    {
        $header = new HTTP_Header($state);

        $this->assertSame($expected, $header->preferred_charset($charsets));
    }

    /**
     * Data provider for test_accepts_encoding_at_quality
     *
     * @return  array
     */
    public function provider_accepts_encoding_at_quality()
    {
        return [
            [
                [
                    'accept-encoding' => 'compress, gzip, blowfish; q=.7, *; q=.5'
                ],
                'gzip',
                false,
                1.0
            ],
            [
                [
                    'accept-encoding' => 'compress, gzip, blowfish; q=.7, *; q=.5'
                ],
                'gzip',
                true,
                1.0
            ],
            [
                [
                    'accept-encoding' => 'compress, gzip, blowfish; q=.7, *; q=.5'
                ],
                'blowfish',
                false,
                0.7
            ],
            [
                [
                    'accept-encoding' => 'compress, gzip, blowfish; q=.7, *; q=.5'
                ],
                'bzip',
                false,
                0.5
            ],
            [
                [
                    'accept-encoding' => 'compress, gzip, blowfish; q=.7, *; q=.5'
                ],
                'bzip',
                true,
                0.0
            ],
        ];
    }

    /**
     * Tests `accepts_encoding_at_quality` parses and returns the correct
     * quality value for Accept-Encoding headers
     *
     * @dataProvider provider_accepts_encoding_at_quality
     *
     * @param   array     state
     * @param   string    encoding
     * @param   boolean   explicit
     * @param   float     expected
     * @return  void
     */
    public function test_accepts_encoding_at_quality(array $state, $encoding, $explicit, $expected)
    {
        $header = new HTTP_Header($state);
        $this->assertSame($expected, $header->accepts_encoding_at_quality($encoding, $explicit));
    }

    /**
     * Data provider for test_preferred_encoding
     *
     * @return  array
     */
    public function provider_preferred_encoding()
    {
        return [
            [
                [
                    'accept-encoding' => 'compress, gzip, blowfish; q=.7, *; q=.5'
                ],
                ['gzip', 'blowfish', 'bzip'],
                false,
                'gzip'
            ],
            [
                [
                    'accept-encoding' => 'compress, gzip, blowfish; q=.7, *; q=.5'
                ],
                ['bzip', 'ROT-13'],
                false,
                'bzip'
            ],
            [
                [
                    'accept-encoding' => 'compress, gzip, blowfish; q=.7, *; q=.5'
                ],
                ['bzip', 'ROT-13'],
                true,
                false
            ],
            [
                [
                    'accept-encoding' => 'compress, gzip, blowfish; q=.2, *; q=.5'
                ],
                ['ROT-13', 'blowfish'],
                false,
                'ROT-13'
            ],
        ];
    }

    /**
     * Tests that `preferred_encoding` parses and returns the correct
     * encoding type
     *
     * @dataProvider provider_preferred_encoding
     *
     * @param   array     state in
     * @param   array     encodings to interrogate
     * @param   boolean   explicit check
     * @param   string    expected output
     * @return  void
     */
    public function test_preferred_encoding(array $state, array $encodings, $explicit, $expected)
    {
        $header = new HTTP_Header($state);
        $this->assertSame($expected, $header->preferred_encoding($encodings, $explicit));
    }

    /**
     * Data provider for test_accepts_language_at_quality
     *
     * @return  array
     */
    public function provider_accepts_language_at_quality()
    {
        return [
            [
                [
                    'accept-language' => 'en-us; q=.9, en-gb; q=.7, en; q=.5, fr-fr; q=.9, fr; q=.8'
                ],
                'en',
                false,
                0.5
            ],
            [
                [
                    'accept-language' => 'en-us; q=.9, en-gb; q=.7, en; q=.5, fr-fr; q=.9, fr; q=.8'
                ],
                'en-gb',
                false,
                0.7
            ],
            [
                [
                    'accept-language' => 'en-us; q=.9, en-gb; q=.7, en; q=.5, fr-fr; q=.9, fr; q=.8'
                ],
                'en',
                true,
                0.5
            ],
            [
                [
                    'accept-language' => 'en-us; q=.9, en-gb; q=.7, en; q=.5, fr-fr; q=.9, fr; q=.8'
                ],
                'fr-ni',
                false,
                0.8
            ],
            [
                [
                    'accept-language' => 'en-us; q=.9, en-gb; q=.7, en; q=.5, fr-fr; q=.9, fr; q=.8'
                ],
                'fr-ni',
                true,
                0.0
            ],
            [
                [
                    'accept-language' => 'en-US'
                ],
                'en-us',
                true,
                1.0
            ],
        ];
    }

    /**
     * Tests `accepts_language_at_quality` parses the Accept-Language header
     * correctly and identifies the correct quality supplied, explicit or not
     *
     * @dataProvider provider_accepts_language_at_quality
     *
     * @param   array    state in
     * @param   string   language to interrogate
     * @param   boolean  explicit check
     * @param   float    expected output
     * @return  void
     */
    public function test_accepts_language_at_quality(array $state, $language, $explicit, $expected)
    {
        $header = new HTTP_Header($state);
        $this->assertSame($expected, $header->accepts_language_at_quality($language, $explicit));
    }

    /**
     * Data provider for test_preferred_language
     *
     * @return  array
     */
    public function provider_preferred_language()
    {
        return [
            [
                [
                    'accept-language' => 'en-us; q=.9, en-gb; q=.7, en; q=.5, fr-fr; q=.9, fr; q=.8'
                ],
                ['en', 'fr', 'en-gb'],
                false,
                'fr'
            ],
            [
                [
                    'accept-language' => 'en-us; q=.9, en-gb; q=.7, en; q=.5, fr-fr; q=.9, fr; q=.8'
                ],
                ['en', 'fr', 'en-gb'],
                true,
                'fr'
            ],
            [
                [
                    'accept-language' => 'en-us; q=.9, en-gb; q=.7, en; q=.5, fr-fr; q=.9, fr; q=.8'
                ],
                ['en-au', 'fr-ni', 'fr'],
                false,
                'fr-ni'
            ],
            [
                [
                    'accept-language' => 'en-us; q=.9, en-gb; q=.7, en; q=.5, fr-fr; q=.9, fr; q=.8'
                ],
                ['en-au', 'fr-ni', 'fr'],
                true,
                'fr'
            ],
            [
                [
                    'accept-language' => 'en-US'
                ],
                ['en-us'],
                true,
                'en-us'
            ],
        ];
    }

    /**
     * Tests that `preferred_language` correctly identifies the right
     * language based on the Accept-Language header and `$explicit` setting
     *
     * @dataProvider provider_preferred_language
     *
     * @param   array    state in
     * @param   array    languages to interrogate
     * @param   boolean  explicit check
     * @param   string   expected output
     * @return  void
     */
    public function test_preferred_language(array $state, array $languages, $explicit, $expected)
    {
        $header = new HTTP_Header($state);
        $this->assertSame($expected, $header->preferred_language($languages, $explicit));
    }

    /**
     * Data provider for test_send_headers
     *
     * @return  array
     */
    public function provider_send_headers()
    {
        $content_type = Kohana::$content_type . '; charset=' . Kohana::$charset;

        return [
            [
                [],
                [
                    'HTTP/1.1 200 OK',
                    'Content-Type: ' . $content_type,
                ],
                false
            ],
            [
                [],
                [
                    'HTTP/1.1 200 OK',
                    'Content-Type: ' . $content_type,
                    'X-Powered-By: ' . Kohana::version(),
                ],
                true
            ],
            [
                [
                    'accept' => 'text/html, text/plain, text/*, */*',
                    'accept-charset' => 'utf-8, utf-10, iso-8859-1',
                    'accept-encoding' => 'compress, gzip',
                    'accept-language' => 'en, en-gb, en-us'
                ],
                [
                    'HTTP/1.1 200 OK',
                    'Accept: text/html, text/plain, text/*, */*',
                    'Accept-Charset: utf-8, utf-10, iso-8859-1',
                    'Accept-Encoding: compress, gzip',
                    'Accept-Language: en, en-gb, en-us',
                    'Content-Type: ' . $content_type,
                ],
                false
            ],
            [
                [
                    'accept' => 'text/html, text/plain, text/*, */*',
                    'accept-charset' => 'utf-8, utf-10, iso-8859-1',
                    'accept-encoding' => 'compress, gzip',
                    'accept-language' => 'en, en-gb, en-us',
                    'content-type' => 'application/json',
                    'x-powered-by' => 'Mohana',
                    'x-ssl-enabled' => 'TRUE'
                ],
                [
                    'HTTP/1.1 200 OK',
                    'Accept: text/html, text/plain, text/*, */*',
                    'Accept-Charset: utf-8, utf-10, iso-8859-1',
                    'Accept-Encoding: compress, gzip',
                    'Accept-Language: en, en-gb, en-us',
                    'Content-Type: application/json',
                    'X-Powered-By: Mohana',
                    'X-Ssl-Enabled: TRUE'
                ],
                true
            ],
        ];
    }

    /**
     * Tests that send headers processes the headers sent to PHP correctly
     *
     * @dataProvider provider_send_headers
     *
     * @param array     state in
     * @param array     expected out
     * @return  void
     * @throws Kohana_Exception
     */
    public function test_send_headers(array $state, array $expected, $expose)
    {
        Kohana::$expose = $expose;

        $response = new Response;
        $response->headers($state);

        $this->assertSame($expected, $response->send_headers(false, [
                $this, 'send_headers_handler'
        ]));
    }

    /**
     * Callback handler for send headers
     *
     * @param   array     headers
     * @param   boolean   replace
     * @return  array
     */
    public function send_headers_handler($response, $headers, $replace)
    {
        return $headers;
    }

}
