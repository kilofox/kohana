<?php

defined('SYSPATH') OR die('Kohana bootstrap needs to be included before tests run');

/**
 * Unit tests for internal methods of userguide controller
 *
 * @group kohana
 * @group kohana.userguide
 * @group kohana.userguide.controller
 *
 * @package    Kohana/Userguide
 * @category   Tests
 * @author     Kohana Team
 * @copyright  (c) 2008-2013 Kohana Team
 * @license    https://kohana.top/license
 */
class Userguide_ControllerTest extends Unittest_TestCase
{
    public function provider_file_finds_markdown_files()
    {
        return [
            ['userguide' . DIRECTORY_SEPARATOR . 'adding', 'guide' . DIRECTORY_SEPARATOR . 'userguide' . DIRECTORY_SEPARATOR . 'adding.md'],
            ['userguide' . DIRECTORY_SEPARATOR . 'adding.md', 'guide' . DIRECTORY_SEPARATOR . 'userguide' . DIRECTORY_SEPARATOR . 'adding.md'],
            ['userguide' . DIRECTORY_SEPARATOR . 'adding.markdown', 'guide' . DIRECTORY_SEPARATOR . 'userguide' . DIRECTORY_SEPARATOR . 'adding.md'],
            ['userguide' . DIRECTORY_SEPARATOR . 'does_not_exist.md', false]
        ];
    }

    /**
     * @dataProvider provider_file_finds_markdown_files
     * @param  string  $page           Page name passed in the URL
     * @param  string  $expected_file  Expected result from Controller_Userguide::file
     */
    public function test_file_finds_markdown_files($page, $expected_file)
    {
        $controller = $this->getMockBuilder('Controller_Userguide')
            ->setMethods(['__construct'])
            ->disableOriginalConstructor()
            ->getMock();

        $path = $controller->file($page);

        // Only verify trailing segments to avoid problems if file overwritten in CFS
        $expected_len = strlen($expected_file);
        $file = substr($path, -$expected_len, $expected_len);

        $this->assertEquals($expected_file, $file);
    }

}
