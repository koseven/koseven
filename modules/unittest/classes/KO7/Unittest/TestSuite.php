<?php

use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\TestResult;

/**
 * A version of the stock PHPUnit Test Suite that supports whitelisting
 * for code coverage filter.
 *
 * @package    KO7/UnitTest
 *
 *
 * @copyright  (c) 2007-2012 Kohana Team
 * @copyright  (c) 2016-2018 Koseven Team
 * @license    https://koseven.dev/LICENSE
 *
 * @codeCoverageIgnore Basic PhpUnit Test Suite. Unit Tests for this File can be ignored safely. If you change
 *                     this class, removing this may be necessary
 */
abstract class KO7_Unittest_TestSuite extends TestSuite
{

	/**
	 * Holds the details of files that should be whitelisted for code coverage
	 * @var array
	 */
	protected $_filter_calls = [
		'addFileToWhitelist' => []
	];

	/**
	 * Runs the tests and collects their result in a TestResult.
	 *
	 * @param  TestResult|NULL $result
	 *
	 * @return ?TestResult
	 * @throws ReflectionException
	 */
	public function run(TestResult $result = NULL): TestResult
	{
		// Get the code coverage filter from the suite's result object
		$coverage = FALSE;
		if ($result !== NULL) {
			$coverage = $result->getCodeCoverage();
		}

		if ($coverage)
		{
			$coverage_filter = $coverage->filter();

			// Apply the white and blacklisting
			foreach ($this->_filter_calls as $method => $args)
			{
				foreach ($args as $arg)
				{
					$coverage_filter->$method($arg);
				}
			}
		}

		return parent::run($result);
	}

	/**
	 * Queues a file to be added to the code coverage whitelist when the suite runs
	 *
	 * @param string $file
	 */
	public function addFileToWhitelist(string $file) : void
	{
		//vendor/phpunit/php-code-coverage/ChangeLog-9.2.md
		//`SebastianBergmann\CodeCoverage\Filter::addFileToWhitelist()` has been renamed to `SebastianBergmann\CodeCoverage\Filter::includeFile()`
		(new SebastianBergmann\CodeCoverage\Filter)->includeFile($file);
		//$this->_filter_calls['addFileToWhitelist'][] = $file;
	}
}
