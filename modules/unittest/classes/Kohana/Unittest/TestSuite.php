<?php

use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\TestResult;

/**
 * A version of the stock PHPUnit testsuite that supports whitelisting
 * for code coverage filter.
 *
 * @package    Kohana/UnitTest
 * @author     Kohana Team
 * @copyright  (c) 2007-2012 Kohana Team
 * @copyright  (c) 2016-2018 Koseven Team
 * @license    https://koseven.ga/LICENSE.md
 */
abstract class Kohana_Unittest_TestSuite extends TestSuite 
{
	/**
	 * Holds the details of files that should be whitelisted for
	 * code coverage
	 * 
	 * @var array
	 */
	protected $_filter_calls = [
		'addFileToWhitelist' => []
	];

	/**
	 * Runs the tests and collects their result in a TestResult.
	 *
	 * @param TestResult|null $result
	 * @return TestResult
	 */
    public function run(?TestResult $result = NULL): TestResult
    {
		// Get the code coverage filter from the suite's result object
		$coverage = $result->getCodeCoverage();
		
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
	 * @param string $file 
	 */
	public function addFileToWhitelist(string $file)
	{
		$this->_filter_calls['addFileToWhitelist'][] = $file;
	}
}
