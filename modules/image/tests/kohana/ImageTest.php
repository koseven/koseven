<?php
/**
 * @package    Kohana/Image
 * @group      kohana
 * @group      kohana.image
 * @category   Test
 * @author     Kohana Team
 * @copyright  (c) Kohana Team
 * @license    https://koseven.ga/LICENSE.md
 */
class Kohana_ImageTest extends Unittest_TestCase {

	public function setUp()
	{
		parent::setUp();

		if ( ! extension_loaded('gd'))
		{
			$this->markTestSkipped('The GD extension is not available.');
		}
	}

	/**
	 * Tests the Image::save() method for files that don't have extensions
	 *
	 * @return  void
	 */
	public function test_save_without_extension()
	{
		$image = Image::factory(MODPATH.'image/tests/test_data/test_image');
		$this->assertTrue($image->save(Kohana::$cache_dir.'/test_image'));

		unlink(Kohana::$cache_dir.'/test_image');
	}

}
