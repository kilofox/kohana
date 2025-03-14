<?php

/**
 * @package    Kohana/Image
 * @group      kohana
 * @group      kohana.image
 * @category   Test
 * @author     Kohana Team
 * @copyright  (c) 2009-2012 Kohana Team
 * @license    http://https://kohana.top/license
 */
class Kohana_ImageTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (!extension_loaded('gd')) {
            $this->markTestSkipped('The GD extension is not available.');
        }
    }

    /**
     * Tests the Image::save() method for files that don't have extensions
     *
     * @return  void
     * @throws Kohana_Exception
     */
    public function test_save_without_extension()
    {
        $image = Image::factory(MODPATH . 'image/tests/test_data/test_image');
        $this->assertTrue($image->save(Kohana::$cache_dir . '/test_image'));

        unlink(Kohana::$cache_dir . '/test_image');
    }

}
