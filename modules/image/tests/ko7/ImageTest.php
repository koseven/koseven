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
		if ( ! extension_loaded('gd'))
		{
			$this->markTestSkipped('The GD extension is not available.');
		}

		parent::setUp();
	}

	/**
	 * Tests the Image::save() method for files that don't have extensions
	 *
	 * @return  void
	 */
	public function test_save_without_extension()
	{
		$image = Image::factory(MODPATH.'image/tests/test_data/test_image');
		$this->assertTrue($image->save(KO7::$cache_dir.'/test_image'));

		@unlink(KO7::$cache_dir.'/test_image');
	}

	/**
	 * Tests if the save to a non existing directory throws an exception
	 */
	public function test_save_to_non_existing_directory()
	{
		$this->expectException(KO7_Exception::class);

		$image = Image::factory(MODPATH.'image/tests/test_data/test_image.gif');
		$this->assertTrue($image->save(KO7::$cache_dir.'/non_existing_directory/non_existing_image.gif'));
	}

	/**
	 * Provides test data for test_crop()
	 *
	 * @return array
	 */
	public function test_load_non_existing_file()
	{
		$this->expectException(KO7_Exception::class);

		$image = Image::factory(MODPATH.'image/tests/test_data/non_existing_image');
	}

	/**
	 * Provides test data for test_load_save_of_unsupported_format()
	 *
	 * @return array
	 */
	public function provider_load_save_of_unsupported_format()
	{
		return [
			['test_image.gif', NULL],
			['test_image.bmp', NULL],
			['test_image.gif', 'Imagick']
		];
	}

	/**
	 * Tests if the load and save of a non existing file throws an exception
	 *
	 * @dataProvider provider_load_save_of_unsupported_format
	 * @param string image_file Image file
	 * @param string driver Image driver
	 */
	public function test_load_save_of_unsupported_format($image_file, $driver = NULL)
	{
		KO7::$config->load('image')->set('default_driver', $driver);

		$this->expectException(KO7_Exception::class);

		$image = Image::factory(MODPATH.'image/tests/test_data/'.$image_file);
		$image->save(KO7::$cache_dir.'/test_image.bmp');

		@unlink(KO7::$cache_dir.'/test_image.bmp');
	}

	/**
	 * Provides test data for test_crop()
	 *
	 * @return array
	 */
	public function provider_to_string()
	{
		return [
			[NULL],

			// Gifs differ
			//['Imagick']
		];
	}

	/**
	 * Tests the conversion to a string
	 *
	 * @dataProvider provider_to_string
	 * @param string driver Image driver
	 */
	public function test_to_string($driver = NULL)
	{
		KO7::$config->load('image')->set('default_driver', $driver);

		$image = Image::factory(MODPATH.'image/tests/test_data/test_image.gif');
		$this->assertSame(file_get_contents(MODPATH.'image/tests/test_data/test_image.gif'), (string) $image);
	}

	/**
	 * Provides test data for test_formats()
	 *
	 * @return array
	 */
	public function provider_formats()
	{
		return [
			['test_image.gif'],
			['test_image.png'],
			['test_image.jpg'],
			// Not supported in Travis CI
			// ['test.webp'],
			['test_image.gif', 'Imagick'],
			['test_image.png', 'Imagick'],
			['test_image.jpg', 'Imagick'],
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

		@unlink(KO7::$cache_dir.'/'.$image_file);
	}

	/**
	 * Provides test data for test_render_of_unsupported_format()
	 *
	 * @return array
	 */
	public function provider_render_types()
	{
		return [
			['test_image.gif', 'png', NULL],
		];
	}

	/**
	 * Tests the rendering to other types
	 *
	 * @dataProvider provider_render_types
	 * @param string image_file Image file
	 * @param string driver Image driver
	 */
	public function test_render_types($image_file, $image_type, $driver = NULL)
	{
		KO7::$config->load('image')->set('default_driver', $driver);

		$image = Image::factory(MODPATH.'image/tests/test_data/'.$image_file);
		$image->render($image_type);

		$this->assertTrue(TRUE);
	}

	/**
	 * Tests overwrite
	 *
	 * @return  void
	 */
	public function test_save_overwrite()
	{
		// Create a copy to overwrite
		if ( ! copy(MODPATH.'image/tests/test_data/test_image.png', KO7::$cache_dir.'/test_image.png'))
		{
			$this->markTestSkipped('The test image could not be copied.');
		}

		$image = Image::factory(KO7::$cache_dir.'/test_image.png');
		$this->assertTrue($image->save());

		@unlink(KO7::$cache_dir.'/test_image.png');
	}

	/**
	 * Tests saving as another format (for GD)
	 *
	 * @return  void
	 */
	public function test_save_as_other_format()
	{
		$image = Image::factory(MODPATH.'image/tests/test_data/test_image.png');
		$this->assertTrue($image->save(KO7::$cache_dir.'/test_image.jpg', 70));

		@unlink(KO7::$cache_dir.'/test_image.jpg');
	}

	/**
	 * Provides test data for test_resize()
	 *
	 * @return array
	 */
	public function provider_resize()
	{
		return [
			[100, 100, NULL, 100, 100],
			[100, 100, Image::AUTO, 100, 100],
			[100, 100, Image::NONE, 100, 100],
			[100, 100, Image::WIDTH, 100, 100],
			[100, 100, Image::HEIGHT, 100, 100],
			[100, 100, Image::INVERSE, 100, 100],
			[100, 100, Image::PRECISE, 100, 100],
			[100, 50, Image::PRECISE, 100, 100],
			[NULL, NULL, Image::NONE, 150, 150],
			[NULL, NULL, Image::NONE, 150, 150, 'Imagick']
		];
	}

	/**
	 * Tests the resize function
	 *
	 * @dataProvider provider_resize
	 * @param string width width of the target image
	 * @param string height height of the target image
	 * @param string master resize mode
	 * @param string expected_width expected width of the resulting image
	 * @param string expected_height expected height of the resulting image
	 * @param string driver Image driver
	 */
	public function test_resize($width, $height, $master, $expected_width, $expected_height, $driver = NULL)
	{
		KO7::$config->load('image')->set('default_driver', $driver);

		$image = Image::factory(MODPATH.'image/tests/test_data/test_image.gif');

		$result = $image->resize($width, $height, $master);

		$this->assertSame($image, $result);
		$this->assertSame($expected_width, $result->width);
		$this->assertSame($expected_height, $result->height);
	}

	/**
	 * Provides test data for test_crop()
	 *
	 * @return array
	 */
	public function provider_crop()
	{
		return [
			[100, 100, NULL, NULL, 100, 100],
			// Original image is 150x150: this should trigger the limits
			[200, 200, NULL, NULL, 150, 150],
			[100, 100, TRUE, NULL, 100, 100],
			[100, 100, -50, NULL, 100, 100],
			[100, 100, NULL, TRUE, 100, 100],
			[100, 100, NULL, -50, 100, 100],
			// Triggers the max_width and max_height protection
			[100, 100, 100, 100, 50, 50],
			[100, 100, NULL, NULL, 100, 100, 'Imagick']
		];
	}

	/**
	 * Tests the crop function
	 *
	 * @dataProvider provider_crop
	 * @param string width width of the target image
	 * @param string height height of the target image
	 * @param string offset_x x offset of the target image
	 * @param string offset_y y offset of the target image
	 * @param string expected_width expected width of the resulting image
	 * @param string expected_height expected height of the resulting image
	 * @param string driver Image driver
	 */
	public function test_crop($width, $height, $offset_x, $offset_y, $expected_width, $expected_height, $driver = NULL)
	{
		KO7::$config->load('image')->set('default_driver', $driver);

		$image = Image::factory(MODPATH.'image/tests/test_data/test_image.gif');

		$result = $image->crop($width, $height, $offset_x, $offset_y);

		$this->assertSame($image, $result);
		$this->assertSame($expected_width, $result->width);
		$this->assertSame($expected_height, $result->height);
	}


	/**
	 * Provides test data for test_rotate()
	 *
	 * @return array
	 */
	public function provider_rotate()
	{
		return [
			[360],
			[-360],
			[360, 'Imagick'],
		];
	}

	/**
	 * Tests the rotate function
	 *
	 * @dataProvider provider_rotate
	 * @param string angle Angle to rotate to
	 * @param string driver Image driver
	 */
	public function test_rotate($angle, $driver = NULL)
	{
		KO7::$config->load('image')->set('default_driver', $driver);

		$image = Image::factory(MODPATH.'image/tests/test_data/test_image.gif');

		$result = $image->rotate($angle);

		$this->assertSame($image, $result);
	}

	/**
	 * Provides test data for test_crop()
	 *
	 * @return array
	 */
	public function provider_flip()
	{
		return [
			[Image::HORIZONTAL],
			[Image::VERTICAL],
			[Image::HORIZONTAL, 'Imagick'],
			[Image::VERTICAL, 'Imagick']
		];
	}

	/**
	 * Tests the flip function
	 *
	 * @dataProvider provider_flip
	 * @param string direction Flip direction
	 * @param string driver Image driver
	 */
	public function test_flip($direction, $driver = NULL)
	{
		KO7::$config->load('image')->set('default_driver', $driver);

		$image = Image::factory(MODPATH.'image/tests/test_data/test_image.gif');

		$result = $image->flip($direction);

		$this->assertSame($image, $result);
	}

	/**
	 * Provides test data for test_crop()
	 *
	 * @return array
	 */
	public function provider_sharpen()
	{
		return [
			[NULL],
			['Imagick']
		];
	}

	/**
	 * Tests the sharpen function
	 * @dataProvider provider_sharpen
	 */
	public function test_sharpen($driver = NULL)
	{
		KO7::$config->load('image')->set('default_driver', $driver);

		$image = Image::factory(MODPATH.'image/tests/test_data/test_image.gif');

		$result = $image->sharpen(20);

		$this->assertSame($image, $result);
	}

	/**
	 * Provides test data for test_reflection()
	 *
	 * @return array
	 */
	public function provider_reflection()
	{
		return [
			[NULL, 90, FALSE],
			[NULL, 110, FALSE],
			[NULL, 90, TRUE],
			[NULL, 90, FALSE, 'Imagick'],
			[NULL, 90, TRUE, 'Imagick']
		];
	}

	/**
	 * Tests the reflection function
	 *
	 * @dataProvider provider_reflection
	 * @param string height height of the target image
	 * @param string opacity Opacity
	 * @param string fade_in Fade in
	 * @param string driver Image driver
	 */
	public function test_reflection($height, $opacity, $fade_in, $driver = NULL)
	{
		KO7::$config->load('image')->set('default_driver', $driver);

		$image = Image::factory(MODPATH.'image/tests/test_data/test_image.gif');

		$result = $image->reflection($height, $opacity, $fade_in);

		$this->assertSame($image, $result);
	}

	/**
	 * Provides test data for test_reflection()
	 *
	 * @return array
	 */
	public function provider_watermark()
	{
		return [
			[NULL, 0, 100],
			[TRUE, 0, 100],
			[-10, 0, 100],
			[0, NULL, 100],
			[0, TRUE, 100],
			[0, -10, 100],
			[0, 0, 90],
			[NULL, 90, FALSE, 'Imagick'],
			[NULL, 90, TRUE, 'Imagick']
		];
	}

	/**
	 * Tests the watermark function
	 *
	 * @dataProvider provider_watermark
	 * @param string height height of the target image
	 * @param string opacity Opacity
	 * @param string fade_in Fade in
	 * @param string driver Image driver
	 */
	public function test_watermark($offset_x, $offset_y, $opacity, $driver = NULL)
	{
		KO7::$config->load('image')->set('default_driver', $driver);

		$image = Image::factory(MODPATH.'image/tests/test_data/test_image.gif');
		$image_watermark = Image::factory(MODPATH.'image/tests/test_data/test_image.png');

		$result = $image->watermark($image_watermark, $offset_x, $offset_y, $opacity);

		$this->assertSame($image, $result);
	}

	/**
	 * Provides test data for test_crop()
	 *
	 * @return array
	 */
	public function provider_background()
	{
		return [
			['#000', 100],
			['#000', 100, 'Imagick'],
		];
	}

	/**
	 * Tests the reflection function
	 *
	 * @dataProvider provider_background
	 */
	public function test_background($color, $opacity, $driver = NULL)
	{
		KO7::$config->load('image')->set('default_driver', $driver);

		$image = Image::factory(MODPATH.'image/tests/test_data/test_image.gif');

		$result = $image->background($color, $opacity);

		$this->assertSame($image, $result);
	}
}