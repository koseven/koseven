<?php
/**
 * @package    KO7/Image
 * @group      ko7
 * @group      ko7.image
 * @category   Test
 * @author     Kohana Team
 * @copyright  (c) Kohana Team
 * @license    https://koseven.ga/LICENSE.md
 */
class KO7_ImageTest extends Unittest_TestCase {

	/**
	 * Default values for the environment, see setEnvironment
	 * @var array
	 */
	protected $environmentDefault =	[
		'image.default_driver' => NULL
	];

	public function setUp(): void
	{
		if ( ! extension_loaded('gd') || ! extension_loaded('imagick'))
		{
			$this->markTestSkipped('The GD extension is not available.');
		}

		parent::setUp();
	}

	/**
	 * Provides test data for test_formats()
	 *
	 * @return array
	 */
	public function provider_formats()
	{
		return [
			['test.webp'],
			['test.webp', 'Imagick'],
		];
	}

	/**
	 * Tests the loading of different supported formats
	 *
	 * @dataProvider provider_formats
	 * @param string image_file Image file
	 * @param string driver Image driver
	 */
	public function test_formats($image_file, $driver = NULL)
	{
		KO7::$config->load('image')->set('default_driver', $driver);

		$image = Image::factory(MODPATH.'image/tests/test_data/'.$image_file);
		$this->assertTrue(TRUE);
	}

	/**
	 * Tests the saving of different supported formats
	 *
	 * @dataProvider provider_formats
	 * @param string image_file Image file
	 * @param string driver Image driver
	 */
	public function test_save_types($image_file, $driver = NULL)
	{
		KO7::$config->load('image')->set('default_driver', $driver);

		$image = Image::factory(MODPATH.'image/tests/test_data/'.$image_file);
		$this->assertTrue($image->save(KO7::$cache_dir.'/'.$image_file));
		unlink(KO7::$cache_dir.'/'.$image_file);
	}
}
