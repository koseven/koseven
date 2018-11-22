<?php

/**
 * Tests Koseven Core
 *
 * @TODO Use a virtual filesystem (see phpunit doc on mocking fs) for find_file etc.
 *
 * @group koseven
 * @group koseven.core
 * @group koseven.core.core
 *
 * @package    Koseven
 * @category   Tests
 * @author     Jeremy Bush <contractfrombelow@gmail.com>
 * @copyright  (c) 2007-2014  Kohana Team
 * @copyright  (c) 2014-2018  Koseven Team
 * @license    https://koseven.ga/LICENSE.md
 */
class Koseven_CoreTest extends Unittest_TestCase
{
	protected $old_modules = [];

	/**
	 * Captures the module list as it was before this test
	 *
	 * @return null
	 */
	// @codingStandardsIgnoreStart
	public function setUp()
	// @codingStandardsIgnoreEnd
	{
		parent::setUp();
		$this->old_modules = Koseven::modules();
	}

	/**
	 * Restores the module list
	 *
	 * @return null
	 */
	// @codingStandardsIgnoreStart
	public function tearDown()
	// @codingStandardsIgnoreEnd
	{
		Koseven::modules($this->old_modules);
	}

	/**
	 * Provides test data for test_sanitize()
	 *
	 * @return array
	 */
	public function provider_sanitize()
	{
		return [
			// $value, $result
			['foo', 'foo'],
			["foo\r\nbar", "foo\nbar"],
			["foo\rbar", "foo\nbar"],
		];
	}

	/**
	 * Tests Koseven::santize()
	 *
	 * @test
	 * @dataProvider provider_sanitize
	 * @covers Koseven::sanitize
	 * @param boolean $value  Input for Koseven::sanitize
	 * @param boolean $result Output for Koseven::sanitize
	 */
	public function test_sanitize($value, $result)
	{
		$this->assertSame($result, Koseven::sanitize($value));
	}

	/**
	 * Passing FALSE for the file extension should prevent appending any extension.
	 * See issue #3214
	 *
	 * @test
	 * @covers  Koseven::find_file
	 */
	public function test_find_file_no_extension()
	{
		// EXT is manually appened to the _file name_, not passed as the extension
		$path = Koseven::find_file('classes', $file = 'Koseven/Core'.EXT, FALSE);

		$this->assertInternalType('string', $path);

		$this->assertStringEndsWith($file, $path);
	}

	/**
	 * If a file can't be found then find_file() should return FALSE if
	 * only a single file was requested, or an empty array if multiple files
	 * (i.e. configuration files) were requested
	 *
	 * @test
	 * @covers Koseven::find_file
	 */
	public function test_find_file_returns_false_or_array_on_failure()
	{
		$this->assertFalse(Koseven::find_file('configy', 'zebra'));

		$this->assertSame([], Koseven::find_file('configy', 'zebra', NULL, TRUE));
	}

	/**
	 * Koseven::list_files() should return an array on success and an empty array on failure
	 *
	 * @test
	 * @covers Koseven::list_files
	 */
	public function test_list_files_returns_array_on_success_and_failure()
	{
		$files = Koseven::list_files('config');

		$this->assertInternalType('array', $files);
		$this->assertGreaterThan(3, count($files));

		$this->assertSame([], Koseven::list_files('geshmuck'));
	}

	/**
	 * Provides test data for testCache()
	 *
	 * @return array
	 */
	public function provider_cache()
	{
		return [
			// $value, $result
			['foo', 'hello, world', 10],
			['bar', NULL, 10],
			['bar', NULL, -10],
		];
	}

	/**
	 * Tests Koseven::cache()
	 *
	 * @test
	 * @dataProvider provider_cache
	 * @covers Koseven::cache
	 * @param boolean $key      Key to cache/get for Koseven::cache
	 * @param boolean $value    Output from Koseven::cache
	 * @param boolean $lifetime Lifetime for Koseven::cache
	 */
	public function test_cache($key, $value, $lifetime)
	{
		Koseven::cache($key, $value, $lifetime);
		$this->assertEquals($value, Koseven::cache($key));
	}

	/**
	 * Tests Koseven::find_file() cache is saved on shutdown.
	 *
	 * @test
	 */
	/*public function test_find_file_cache_saved()
	{
		$old_caching     = Koseven::$caching;
		$old_errors      = Koseven::$errors;
		Koseven::$caching = TRUE;
		Koseven::$errors  = FALSE;

		// trigger find_file() so Koseven::$_files_changed is set to TRUE
		Koseven::find_file('abc', 'def');

		// trigger shutdown so koseven write to file cache
		Koseven::shutdown_handler();

		$this->assertInternalType('array', Koseven::file_cache('Koseven::find_file()'));

		Koseven::$caching = $old_caching;
		Koseven::$errors  = $old_errors;
	}*/

	/**
	 * Provides test data for test_message()
	 *
	 * @return array
	 */
	public function provider_message()
	{
		return [
			['no_message_file', 'anything', 'default', 'default'],
			['no_message_file', NULL, 'anything', []],
			['koseven_core_message_tests', 'bottom_only', 'anything', 'inherited bottom message'],
			['koseven_core_message_tests', 'cfs_replaced', 'anything', 'overriding cfs_replaced message'],
			['koseven_core_message_tests', 'top_only', 'anything', 'top only message'],
			['koseven_core_message_tests', 'missing', 'default', 'default'],
			['koseven_core_message_tests', NULL, 'anything',
				[
					'bottom_only'  => 'inherited bottom message',
					'cfs_replaced' => 'overriding cfs_replaced message',
					'top_only'     => 'top only message'
				]
			],
		];
	}

	/**
	 * Tests Koseven::message()
	 *
	 * @test
	 * @dataProvider provider_message
	 * @covers       Koseven::message
	 * @param string $file     to pass to Koseven::message
	 * @param string $key      to pass to Koseven::message
	 * @param string $default  to pass to Koseven::message
	 * @param string $expected Output for Koseven::message
	 */
	public function test_message($file, $key, $default, $expected)
	{
		$test_path = realpath(dirname(__FILE__).'/../test_data/message_tests');
		Koseven::modules(['top' => "$test_path/top_module", 'bottom' => "$test_path/bottom_module"]);

		$this->assertEquals($expected, Koseven::message($file, $key, $default, $expected));
	}

	/**
	 * Provides test data for test_error_handler()
	 *
	 * @return array
	 */
	public function provider_error_handler()
	{
		return [
			[1, 'Foobar', 'foobar.php', __LINE__],
		];
	}

	/**
	 * Tests Koseven::error_handler()
	 *
	 * @test
	 * @dataProvider provider_error_handler
	 * @covers Koseven::error_handler
	 * @param boolean $code  Input for Koseven::sanitize
	 * @param boolean $error  Input for Koseven::sanitize
	 * @param boolean $file  Input for Koseven::sanitize
	 * @param boolean $line Output for Koseven::sanitize
	 */
	public function test_error_handler($code, $error, $file, $line)
	{
		$error_level = error_reporting();
		error_reporting(E_ALL);
		try
		{
			Koseven::error_handler($code, $error, $file, $line);
		}
		catch (Exception $e)
		{
			$this->assertEquals($code, $e->getCode());
			$this->assertEquals($error, $e->getMessage());
		}
		error_reporting($error_level);
	}

	/**
	 * Provides test data for test_modules_sets_and_returns_valid_modules()
	 *
	 * @return array
	 */
	public function provider_modules_detects_invalid_modules()
	{
		return [
			[['unittest' => MODPATH.'fo0bar']],
			[['unittest' => MODPATH.'unittest', 'fo0bar' => MODPATH.'fo0bar']],
		];
	}

	/**
	 * Tests Koseven::modules()
	 *
	 * @test
	 * @dataProvider provider_modules_detects_invalid_modules
	 * @expectedException Koseven_Exception
	 * @param boolean $source   Input for Koseven::modules
	 *
	 */
	public function test_modules_detects_invalid_modules($source)
	{
		$modules = Koseven::modules();

		try
		{
			Koseven::modules($source);
		}
		catch(Exception $e)
		{
			// Restore modules
			Koseven::modules($modules);

			throw $e;
		}

		// Restore modules
		Koseven::modules($modules);
	}

	/**
	 * Provides test data for test_modules_sets_and_returns_valid_modules()
	 *
	 * @return array
	 */
	public function provider_modules_sets_and_returns_valid_modules()
	{
		return [
			[[], []],
			[['module' => __DIR__], ['module' => $this->dirSeparator(__DIR__.'/')]],
		];
	}

	/**
	 * Tests Koseven::modules()
	 *
	 * @test
	 * @dataProvider provider_modules_sets_and_returns_valid_modules
	 * @param boolean $source   Input for Koseven::modules
	 * @param boolean $expected Output for Koseven::modules
	 */
	public function test_modules_sets_and_returns_valid_modules($source, $expected)
	{
		$modules = Koseven::modules();

		try
		{
			$this->assertEquals($expected, Koseven::modules($source));
		}
		catch(Exception $e)
		{
			Koseven::modules($modules);

			throw $e;
		}

		Koseven::modules($modules);
	}

	/**
	 * To make the tests as portable as possible this just tests that
	 * you get an array of modules when you can Koseven::modules() and that
	 * said array contains unittest
	 *
	 * @test
	 * @covers Koseven::modules
	 */
	public function test_modules_returns_array_of_modules()
	{
		$modules = Koseven::modules();

		$this->assertInternalType('array', $modules);

		$this->assertArrayHasKey('unittest', $modules);
	}

	/**
	 * Tests Koseven::include_paths()
	 *
	 * The include paths must contain the apppath and syspath
	 * @test
	 * @covers Koseven::include_paths
	 */
	public function test_include_paths()
	{
		$include_paths = Koseven::include_paths();
		$modules       = Koseven::modules();

		$this->assertInternalType('array', $include_paths);

		// We must have at least 2 items in include paths (APP / SYS)
		$this->assertGreaterThan(2, count($include_paths));
		// Make sure said paths are in the include paths
		// And make sure they're in the correct positions
		$this->assertSame(APPPATH, reset($include_paths));
		$this->assertSame(SYSPATH, end($include_paths));

		foreach ($modules as $module)
		{
			$this->assertContains($module, $include_paths);
		}
	}
}

