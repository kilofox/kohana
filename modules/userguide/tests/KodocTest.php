<?php

/**
 * @group kohana
 * @group kohana.userguide
 *
 * @package    Kohana/Userguide
 * @author     Kohana Team
 * @copyright  (c) 2008-2013 Kohana Team
 * @license    https://kohana.top/license
 */
class Kohana_KodocTest extends PHPUnit\Framework\TestCase
{
    public function provider_parse_basic()
    {
        return [
            [
                <<<'COMMENT'
/**
 * Description
 */
COMMENT
                ,
                ["<p>Description</p>\n", []],
            ],
            [
                <<<'COMMENT'
/**
 * Description spanning
 * multiple lines
 */
COMMENT
                ,
                ["<p>Description spanning\nmultiple lines</p>\n", []],
            ],
            [
                <<<'COMMENT'
/**
 * Description including
 *
 *     a code block
 */
COMMENT
                ,
                ["<p>Description including</p>\n\n<pre><code>a code block\n</code></pre>\n", []],
            ],
            [
                <<<'COMMENT'
	/**
	 * Indented
	 */
COMMENT
                ,
                ["<p>Indented</p>\n", []],
            ],
            [
                <<<'COMMENT'
/**
 * @tag Content
 */
COMMENT
                ,
                ['', ['tag' => ['Content']]],
            ],
            [
                <<<'COMMENT'
/**
 * @tag Multiple
 * @tag Tags
 */
COMMENT
                ,
                ['', ['tag' => ['Multiple', 'Tags']]],
            ],
            [
                <<<'COMMENT'
/**
 * Description with tag
 * @tag Content
 */
COMMENT
                ,
                [
                    "<p>Description with tag</p>\n",
                    ['tag' => ['Content']],
                ],
            ],
            [
                <<<'COMMENT'
/**
 * @trailingspace
 */
COMMENT
                ,
                ['', ['trailingspace' => ['']]],
            ],
            [
                <<<'COMMENT'
/**
 * @tag Content that spans
 * multiple lines
 */
COMMENT
                ,
                [
                    '',
                    ['tag' => ["Content that spans\nmultiple lines"]],
                ],
            ],
            [
                <<<'COMMENT'
/**
 * @tag Content that spans
 *    multiple lines indented
 */
COMMENT
                ,
                [
                    '',
                    ['tag' => ["Content that spans\n   multiple lines indented"]],
                ],
            ],
        ];
    }

    /**
     * @covers          Kohana_Kodoc::parse
     *
     * @dataProvider    provider_parse_basic
     *
     * @param string $comment Argument to the method
     * @param array $expected Expected result
     * @throws Kohana_Exception
     */
    public function test_parse_basic($comment, $expected)
    {
        $this->assertSame($expected, Kodoc::parse($comment));
    }

    public function provider_parse_tags()
    {
        $route_api = Route::get('docs/api');

        return [
            [
                <<<'COMMENT'
/**
 * @access public
 */
COMMENT
                ,
                ['', []],
            ],
            [
                <<<'COMMENT'
/**
 * @copyright Some plain text
 */
COMMENT
                ,
                ['', ['copyright' => ['Some plain text']]],
            ],
            [
                <<<'COMMENT'
/**
 * @copyright (c) 2008-2013 Kohana Team
 */
COMMENT
                ,
                ['', ['copyright' => ['&copy; 2008-2013 Kohana Team']]],
            ],
            [
                <<<'COMMENT'
/**
 * @license Kohana
 */
COMMENT
                ,
                ['', ['license' => ['Kohana']]],
            ],
            [
                <<<'COMMENT'
/**
 * @license https://kohana.top/license
 */
COMMENT
                ,
                ['', ['license' => ['<a href="https://kohana.top/license">https://kohana.top/license</a>']]],
            ],
            [
                <<<'COMMENT'
/**
 * @link https://kohana.top
 */
COMMENT
                ,
                ['', ['link' => ['<a href="https://kohana.top">https://kohana.top</a>']]],
            ],
            [
                <<<'COMMENT'
/**
 * @link https://kohana.top Description
 */
COMMENT
                ,
                ['', ['link' => ['<a href="https://kohana.top">Description</a>']]],
            ],
            [
                <<<'COMMENT'
/**
 * @see MyClass
 */
COMMENT
                ,
                [
                    '',
                    [
                        'see' => [
                            '<a href="' . URL::site($route_api->uri(['class' => 'MyClass'])) . '">MyClass</a>',
                        ],
                    ],
                ],
            ],
            [
                <<<'COMMENT'
/**
 * @see MyClass::method()
 */
COMMENT
                ,
                [
                    '',
                    [
                        'see' => [
                            '<a href="' . URL::site($route_api->uri(['class' => 'MyClass']) . '#method') . '">MyClass::method()</a>',
                        ],
                    ],
                ],
            ],
            [
                <<<'COMMENT'
/**
 * @throws Exception
 */
COMMENT
                ,
                [
                    '',
                    [
                        'throws' => [
                            '<a href="' . URL::site($route_api->uri(['class' => 'Exception'])) . '">Exception</a>',
                        ],
                    ],
                ],
            ],
            [
                <<<'COMMENT'
/**
 * @throws Exception During failure
 */
COMMENT
                ,
                [
                    '',
                    [
                        'throws' => [
                            '<a href="' . URL::site($route_api->uri(['class' => 'Exception'])) . '">Exception</a> During failure',
                        ],
                    ],
                ],
            ],
            [
                <<<'COMMENT'
/**
 * @uses MyClass
 */
COMMENT
                ,
                [
                    '',
                    [
                        'uses' => [
                            '<a href="' . URL::site($route_api->uri(['class' => 'MyClass'])) . '">MyClass</a>',
                        ],
                    ],
                ],
            ],
            [
                <<<'COMMENT'
/**
 * @uses MyClass::method()
 */
COMMENT
                ,
                [
                    '',
                    [
                        'uses' => [
                            '<a href="' . URL::site($route_api->uri(['class' => 'MyClass']) . '#method') . '">MyClass::method()</a>',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @covers          Kohana_Kodoc::format_tag
     * @covers          Kohana_Kodoc::parse
     *
     * @dataProvider    provider_parse_tags
     *
     * @param string $comment Argument to the method
     * @param array $expected Expected result
     * @throws Kohana_Exception
     */
    public function test_parse_tags($comment, $expected)
    {
        $this->assertSame($expected, Kodoc::parse($comment));
    }

    /**
     * Provides test data for test_transparent_classes
     * @return array
     */
    public function provider_transparent_classes()
    {
        return [
            // Kohana_Core is a special case
            [
                'Kohana',
                'Kohana_Core',
                null
            ],
            [
                'Controller_Template',
                'Kohana_Controller_Template',
                null
            ],
            [
                'Controller_Template',
                'Kohana_Controller_Template',
                [
                    'Kohana_Controller_Template' => 'Kohana_Controller_Template',
                    'Controller_Template' => 'Controller_Template'
                ]
            ],
            [
                false,
                'Kohana_Controller_Template',
                [
                    'Kohana_Controller_Template' => 'Kohana_Controller_Template'
                ]
            ],
            [
                false,
                'Controller_Template',
                null
            ],
        ];
    }

    /**
     * Tests Kodoc::is_transparent
     *
     * Checks that a selection of transparent and non-transparent classes give expected results
     *
     * @group kohana.userguide.3529-configurable-transparent-classes
     * @dataProvider provider_transparent_classes
     * @param mixed $expected
     * @param string $class
     * @param array $classes
     * @throws Kohana_Exception
     */
    public function test_transparent_classes($expected, $class, $classes)
    {
        $result = Kodoc::is_transparent($class, $classes);
        $this->assertSame($expected, $result);
    }

}
