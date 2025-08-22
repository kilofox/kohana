<?php

/**
 * Test for feed helper
 *
 * @group kohana
 * @group kohana.core
 * @group kohana.core.feed
 *
 * @package    Kohana
 * @category   Tests
 * @author     Kohana Team
 * @author     Jeremy Bush <contractfrombelow@gmail.com>
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    https://kohana.top/license
 */
class Kohana_FeedTest extends Unittest_TestCase
{
    /**
     * Sets up the environment
     */
    // @codingStandardsIgnoreStart
    public function setUp()
    // @codingStandardsIgnoreEnd
    {
        parent::setUp();
        Kohana::$config->load('url')->set('trusted_hosts', ['localhost']);
    }

    /**
     * Provides test data for test_parse()
     *
     * @return array
     */
    public function provider_parse()
    {
        return [
            // $source, $expected
            [
                realpath(__DIR__ . '/../test_data/feeds/activity.atom'),
                [
                    'Proposals (Political/Workflow) #4839 (New)',
                    'Proposals (Political/Workflow) #4782'
                ]
            ],
            [
                realpath(__DIR__ . '/../test_data/feeds/example.rss20'),
                [
                    'Example entry'
                ]
            ],
        ];
    }

    /**
     * Tests that Feed::parse gets the correct number of elements
     *
     * @test
     * @dataProvider provider_parse
     * @covers       feed::parse
     * @param string $source URL to test
     * @param $expected_titles
     * @throws HTTP_Exception_404
     * @throws Kohana_Exception
     * @throws Request_Exception
     */
    public function test_parse(string $source, $expected_titles)
    {
        $titles = [];
        foreach (Feed::parse($source) as $item) {
            $titles[] = $item['title'];
        }

        $this->assertSame($expected_titles, $titles);
    }

    /**
     * Provides test data for test_create()
     *
     * @return array
     */
    public function provider_create()
    {
        $info = [
            'pubDate' => 123,
            'image' => [
                'link' => 'https://kohana.top/image.png',
                'url' => 'https://kohana.top/',
                'title' => 'title'
            ]
        ];

        return [
            [
                $info,
                ['foo' => ['foo' => 'bar', 'pubDate' => 123, 'link' => 'foo']],
                ['_SERVER' => ['HTTP_HOST' => 'localhost'] + $_SERVER],
                ['channel > item > foo', 'bar'],
                [
                    $this->matcher_composer($info, 'image', 'link'),
                    $this->matcher_composer($info, 'image', 'url'),
                    $this->matcher_composer($info, 'image', 'title')
                ]
            ],
        ];
    }

    /**
     * Helper for handy matcher composing
     *
     * @param array $data
     * @param string $tag
     * @param string $child
     * @return array
     */
    private function matcher_composer(array $data, string $tag, string $child)
    {
        return [
            'channel > ' . $tag . ' > ' . $child,
            $data[$tag][$child]
        ];
    }

    /**
     * @test
     *
     * @dataProvider provider_create
     *
     * @covers       feed::create
     *
     * @param array $info info to pass
     * @param array $items items to add
     * @param array $enviroment Server environment data.
     * @param array $matcher_item XML matcher structure for the main item.
     * @param array $matchers_image Array of XML matcher structures for image elements.
     * @throws Kohana_Exception
     * @throws ReflectionException
     */
    public function test_create(array $info, array $items, array $enviroment, array $matcher_item, array $matchers_image)
    {
        $this->setEnvironment($enviroment);

        $this->assertSelectEquals($matcher_item[0], $matcher_item[1], true, Feed::create($info, $items), '', false);

        foreach ($matchers_image as $matcher_image) {
            $this->assertSelectEquals($matcher_image[0], $matcher_image[1], true, Feed::create($info, $items), '', false);
        }
    }

}
