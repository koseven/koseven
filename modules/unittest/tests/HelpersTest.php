<?php
/**
 * Testing Unittest Module Helper Class
 * Very Important as other Unittests depend on that
 */
class HelpersTest extends Unittest_TestCase {

	/**
	 * Replaces all / in string with directory Separator
	 */
	public function test_dir_separator()
	{
		$expected = 'test'.DIRECTORY_SEPARATOR.'dir';
		self::assertSame($expected, Kohana_Unittest_Helpers::dir_separator('test/dir'));
	}

	/**
	 * Tests cleaning cache dir which should happen recursivley.
	 * Following structure will be created to test that:
	 *
	 *   - Folder
	 *     .hidden-file
	 *     - Folder2
	 *       - file2
	 *   - file
	 */
	public function test_clean_cache_dir()
	{
		// Kohana Cache directory
		$cacheDir = Kohana::$cache_dir;

		 // Create Test Directories and Files
		$folder = $cacheDir . DIRECTORY_SEPARATOR . 'Folder';
		$folder2 = $folder . DIRECTORY_SEPARATOR . 'Folder2';
		$file = $folder . DIRECTORY_SEPARATOR . '.hidden-file';
		$file2 = $cacheDir . DIRECTORY_SEPARATOR . 'file';
		$file3 = $folder2 . DIRECTORY_SEPARATOR . 'file2';

		if ( ! mkdir($folder) || ! mkdir($folder2) || ! touch($file) || ! touch($file2) || ! touch($file3)) {
			self::fail('Could not create Test Files. Please make sure your cache dir is writable');
		}

		Kohana_Unittest_Helpers::clean_cache_dir();
		$files = scandir($cacheDir, 0);

		// Only files left should now be: '.','..','.gitignore'
		self::assertCount(3, $files);
	}

	/**
	 * Test setting configuration options via set environment
	 * @throws Kohana_Exception
	 * @throws ReflectionException
	 */
	public function test_set_environment_config_options()
	{
		// Init
		$config = [
			'cache.default' => 'test'
		];
		$helpers = new Kohana_Unittest_Helpers();

		// Set new environment and check if set correctly
		$helpers->set_environment($config);
		self::assertSame('test', Kohana::$config->load('cache')->get('default'));

		// Restore old environment and check if restored
		$helpers->restore_environment();
		self::assertNotSame('test', Kohana::$config->load('cache')->get('default'));
	}
}
