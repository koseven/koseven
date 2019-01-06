<?php

use PHPUnit\Framework\TestCase;

/**
 * A version of the stock PHPUnit testcase that includes some extra helpers
 * and default settings
 *
 * @package    Kohana/UnitTest
 *
 * @author     Koseven Team
 * @copyright  (c) 2007-2012 Kohana Team
 * @copyright  (c) 2016-2018 Koseven Team
 * @license    https://koseven.ga/LICENSE.md
 */
abstract class Kohana_Unittest_TestCase extends TestCase {

	/**
	 * Make sure PHPUnit does not back up globals
	 * @var boolean
	 */
	protected $backupGlobals = FALSE;

	/**
	 * A set of Unittest helpers that are shared between normal / database testcases
	 * @var Kohana_Unittest_Helpers
	 */
	protected $_helpers;

	/**
	 * A default set of environment to be applied before each test
	 * @var array
	 */
	protected $environmentDefault = [];

	/**
	 * Creates a predefined environment using the default environment
	 *
	 * Extending classes that have their own setUp() should call
	 * parent::setUp()
	 *
	 * @throws Kohana_Exception
	 * @throws ReflectionException
	 */
	public function setUp()
	{
		$this->_helpers = new Unittest_Helpers;

		$this->setEnvironment($this->environmentDefault);

		parent::setUp();
	}

	/**
	 * Restores the original environment overridden with setEnvironment()
	 *
	 * Extending classes that have their own tearDown()
	 * should call parent::tearDown()
	 * @throws Kohana_Exception
	 * @throws ReflectionException
	 */
	public function tearDown()
	{
		$this->_helpers->restore_environment();

		parent::tearDown();
	}

	/**
	 * Removes all kohana related cache files in the cache directory
	 */
	public function cleanCacheDir()
	{
		Unittest_Helpers::clean_cache_dir();
	}

	/**
	 * Helper function that replaces all occurrences of '/' with
	 * the OS-specific directory separator
	 *
	 * @param  string $path The path to act on
	 * @return string
	 */
	public function dirSeparator(string $path) : string {
		return Unittest_Helpers::dir_separator($path);
	}

	/**
	 * Allows easy setting & backing up of Environment config
	 *
	 * Option types are checked in the following order:
	 *
	 * - Server Var
	 * - Static Variable
	 * - Config option
	 *
	 * @param  array $environment List of environment to set
	 * @return bool
	 * @throws Kohana_Exception
	 * @throws ReflectionException
	 */
	public function setEnvironment(array $environment) : bool
	{
		return $this->_helpers->set_environment($environment);
	}

	/**
	 * Check for internet connectivity
	 * @return boolean Whether an internet connection is available
	 */
	public function hasInternet() : bool
	{
		return Unittest_Helpers::has_internet();
	}

	/**
	 * Evaluate an HTML or XML string and assert its structure and/or contents.
	 *
	 * @param array  $matcher
	 * @param string $actual
	 * @param string $message
	 * @param bool   $isHtml
	 *
	 * @deprecated since 4.0
	 */
	public static function assertTag(array $matcher, string $actual, $message = NULL, $isHtml = NULL)
	{
		$matched = static::tag_match($matcher, $actual, $isHtml ?? TRUE);
		static::assertTrue($matched, $message ?? '');
	}

	/**
	 * Helper function to match HTML string tags against certain criteria
	 *
	 * @param array  $matcher
	 * @param string $actual
	 * @param bool 	 $isHtml
	 *
	 * @return bool TRUE if there is a match FALSE otherwise
	 *
	 * @deprecated since 4.0
	 */
	protected static function tag_match(array $matcher, string $actual, $isHtml = NULL) : bool
	{
        $tags = PHPUnit\Util\Xml::load($actual, $isHtml ?? TRUE)->getElementsByTagName($matcher['tag']);

		return count($tags) > 0 && $tags[0] instanceof DOMNode;
	}
}
