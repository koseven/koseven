<?php
/**
 * PHPUnit Test Suite for Kohana
 *
 * @package    Kohana/UnitTest
 *
 * @author	   Koseven Team, Paul Banks, BRMatt <matthew@sigswitch.com>
 * @copyright  (c) 2007-2012 Kohana Team
 * @copyright  (c) 2016-2018 Koseven Team
 * @license    https://koseven.ga/LICENSE.md
 *
 * @codeCoverageIgnore Basic Test Suite / PHPUnit Test loader. Individual functions tested by there self.
 */
class Kohana_Unittest_Tests {

	/**
	 * Holds Cached Files
	 * @var array
	 */
	protected static $cache = [];

	/**
	 * Loads test files that don't match the naming convention of kohana
	 * @param string $class
	 *
	 * @codeCoverageIgnore Simple Autlo-Loader find_file is tested in system tests, no need to test
	 */
	public static function autoload(string $class)
	{
		$file = Kohana::find_file('tests', str_replace('_', '/', $class));

		if ($file)
		{
			require_once $file;
		}
	}

	/**
	 * Configures the environment for testing
	 *
	 * Does the following:
	 *
	 * - Restores exception and error handlers (for cli)
	 * - registers an autoloader to load test files
	 */
	public static function configure_environment()
	{
		restore_exception_handler();
		restore_error_handler();
		spl_autoload_register(['Unittest_tests', 'autoload']);
	}

	/**
	 * Creates the test suite for kohana
	 *
	 * @return Unittest_TestSuite
	 * @throws Kohana_Exception
	 */
	public static function suite(): \Unittest_TestSuite
	{

		self::configure_environment();

		$suite = new Unittest_TestSuite;

		// Load the whitelist and blacklist for code coverage
		$config = Kohana::$config->load('unittest');

		if ($config->use_whitelist)
		{
			self::whitelist($suite);
		}

		// Add tests
		$files = Kohana::list_files('tests');
		self::addTests($suite, $files);

		return $suite;
	}

	/**
	 * Add files to test suite $suite
	 *
	 * Uses recursion to scan subdirectories
	 *
	 * @param Unittest_TestSuite  $suite   The test suite to add to
	 * @param array               $files   Array of files to test
	 */
	public static function addTests(Unittest_TestSuite $suite, array $files)
	{
		foreach ($files as $path => $file)
		{
			if (is_array($file))
			{
				if ($path !== 'tests'.DIRECTORY_SEPARATOR.'test_data')
				{
					self::addTests($suite, $file);
				}
			}
			elseif (is_file($file) && substr($file, -strlen(EXT)) === EXT)
			{
				$suite->addTestFile($file);
			}
		}
	}

	/**
	 * Sets the whitelist
	 *
	 * If no directories are provided then the function'll load the whitelist
	 * set in the config file
	 *
	 * @param Unittest_Testsuite   $suite 		  Suite to load the whitelist into
	 *
	 * @throws Kohana_Exception
	 */
	public static function whitelist(Unittest_TestSuite $suite = NULL)
	{
		$directories = self::get_config_whitelist();

		if (count($directories))
		{
			foreach ($directories as &$directory)
			{
				$directory = realpath($directory).'/';
			}
			unset($directory);

			// Only whitelist the "top" files in the cascading filesystem
			self::set_whitelist(Kohana::list_files('classes', $directories), $suite);
		}
	}

	/**
	 * Works out the whitelist from the config
	 * Used only on the CLI
	 *
	 * @returns array Array of directories to whitelist
	 * @throws Kohana_Exception
	 */
	protected static function get_config_whitelist()
	{
		$config = Kohana::$config->load('unittest');
		$directories = [];

		if ($config->whitelist['app'])
		{
			$directories['k_app'] = APPPATH;
		}

		$modules = $config->whitelist['modules'];

		if ($modules)
		{
			$k_modules = Kohana::modules();

			// Have to do this because kohana merges config...
			// If you want to include all modules & override defaults then TRUE must be the first
			// value in the modules array of your app/config/unittest file
			if (array_search(TRUE, $modules, TRUE) === (count($modules) - 1))
			{
				$modules = $k_modules;
			}
			elseif (in_array(FALSE, $modules, TRUE)) {
				// modules are disabled
				$modules = [];
			}
			else {
				$modules = array_intersect_key($k_modules, array_combine($modules, $modules));
			}

			$directories += $modules;
		}

		if ($config->whitelist['system'])
		{
			$directories['k_sys'] = SYSPATH;
		}

		return $directories;
	}

	/**
	 * Recursively whitelists an array of files
	 *
	 * @param array $files Array of files to whitelist
	 * @param Unittest_TestSuite $suite Suite to load the whitelist into
	 */
	protected static function set_whitelist($files, Unittest_TestSuite $suite)
	{
		foreach ($files as $file)
		{
			if (is_array($file))
			{
				self::set_whitelist($file, $suite);
			}
			else
			{
				if ( ! isset(self::$cache[$file]))
				{
					$relative_path = substr($file, strrpos($file, 'classes'.DIRECTORY_SEPARATOR) + 8, -strlen(EXT));
					$cascading_file = Kohana::find_file('classes', $relative_path);

					// The theory is that if this file is the highest one in the cascading filesystem
					// then it's safe to whitelist
					self::$cache[$file] =  ($cascading_file === $file);
				}
				$suite->addFileToWhitelist($file);
			}
		}
	}
}
