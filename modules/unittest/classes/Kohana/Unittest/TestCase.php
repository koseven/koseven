<?php
use PHPUnit\Framework\TestCase;
/**
 * A version of the stock PHPUnit testcase that includes some extra helpers
 * and default settings
 */
abstract class Kohana_Unittest_TestCase extends TestCase {
	
	/**
	 * Make sure PHPUnit backs up globals
	 * @var boolean
	 */
	protected $backupGlobals = FALSE;

	/**
	 * A set of unittest helpers that are shared between normal / database
	 * testcases
	 * @var Kohana_Unittest_Helpers
	 */
	protected $_helpers = NULL;

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
	 * @codeCoverageIgnore
	 */
	public function setUp()
	{
		$this->_helpers = new Unittest_Helpers;

		$this->setEnvironment($this->environmentDefault);
	}

	/**
	 * Restores the original environment overriden with setEnvironment()
	 *
	 * Extending classes that have their own tearDown()
	 * should call parent::tearDown()
	 * 
	 * @codeCoverageIgnore
	 */
	public function tearDown()
	{
		$this->_helpers->restore_environment();
	}

	/**
	 * Removes all kohana related cache files in the cache directory
	 */
	public function cleanCacheDir()
	{
		return Unittest_Helpers::clean_cache_dir();
	}

	/**
	 * Helper function that replaces all occurences of '/' with
	 * the OS-specific directory separator
	 *
	 * @param string $path The path to act on
	 * @return string
	 */
	public function dirSeparator($path)
	{
		return Unittest_Helpers::dir_separator($path);
	}

	/**
	 * Allows easy setting & backing up of enviroment config
	 *
	 * Option types are checked in the following order:
	 *
	 * * Server Var
	 * * Static Variable
	 * * Config option
	 *
	 * @codeCoverageIgnore
	 * @param array $environment List of environment to set
	 */
	public function setEnvironment(array $environment)
	{
		return $this->_helpers->set_environment($environment);
	}

	/**
	 * Check for internet connectivity
	 *
	 * @return boolean Whether an internet connection is available
	 */
	public function hasInternet()
	{
		return Unittest_Helpers::has_internet();
	}

	/**
	 * Evaluate an HTML or XML string and assert its structure and/or contents.
	 *
	 * NOTE:
	 * Overriding this method to remove the deprecation error
	 * when tested with PHPUnit 4.2.0+
	 *
	 * TODO:
	 * this should be removed when phpunit-dom-assertions gets released
	 * https://github.com/phpunit/phpunit-dom-assertions
	 *
	 * @param array $matcher
	 * @param string $actual
	 * @param string $message
	 * @param bool $isHtml
	 * @uses Unittest_TestCase::tag_match
	 */
	public static function assertTag($matcher, $actual, $message = '', $isHtml = true)
	{
		//trigger_error(__METHOD__ . ' is deprecated', E_USER_DEPRECATED);

		$matched = static::tag_match($matcher, $actual, $message, $isHtml);
		static::assertTrue($matched, $message);
	}

	/**
	 * This assertion is the exact opposite of assertTag
	 *
	 * Rather than asserting that $matcher results in a match, it asserts that
	 * $matcher does not match
	 *
	 * NOTE:
	 * Overriding this method to remove the deprecation error
	 * when tested with PHPUnit 4.2.0+
	 *
	 * TODO:
	 * this should be removed when phpunit-dom-assertions gets released
	 * https://github.com/phpunit/phpunit-dom-assertions
	 *
	 * @param array $matcher
	 * @param string $actual
	 * @param string $message
	 * @param bool $isHtml
	 * @uses Unittest_TestCase::tag_match
	 */
	public static function assertNotTag($matcher, $actual, $message = '', $isHtml = true)
	{

		//trigger_error(__METHOD__ . ' is deprecated', E_USER_DEPRECATED);
        
		$matched = static::tag_match($matcher, $actual, $message, $isHtml);
		static::assertTrue($matched, $message);
	}

	/**
	 * Helper function to match HTML string tags against certain criteria
	 *
	 * TODO:
	 * this should be removed when phpunit-dom-assertions gets released
	 * https://github.com/phpunit/phpunit-dom-assertions
	 *
	 * @param array $matcher
	 * @param string $actual
	 * @param string $message
	 * @param bool $isHtml
	 * @return bool TRUE if there is a match FALSE otherwise
	 */
	protected static function tag_match($matcher, $actual, $message = '', $isHtml = true)
	{
		$dom = PHPUnit\Util\Xml::load($actual, $isHtml);
        $tags = $dom->getElementsByTagName($matcher['tag']);
        
		return count($tags) > 0 && $tags[0] instanceof DOMNode;
	}
}
