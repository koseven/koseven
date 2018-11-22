<?php

/**
 * Tests Koseven Exception Class
 *
 * @group koseven
 * @group koseven.core
 * @group koseven.core.exception
 *
 * @package    Koseven
 * @category   Tests
 * @copyright  (c) 2007-2014  Kohana Team
 * @copyright  (c) 2014-2018  Koseven Team
 * @license    https://koseven.ga/LICENSE.md
 */
class Koseven_ExceptionTest extends Unittest_TestCase
{
	/**
	 * Provides test data for test_constructor()
	 *
	 * @return array
	 */
	public function provider_constructor()
	{
		return [
			[[''], '', 0],
			[[':a'], ':a', 0],

			[[':a', NULL], ':a', 0],
			[[':a', []], ':a', 0],
			[[':a', [':a' => 'b']], 'b', 0],
			[[':a :b', [':a' => 'c', ':b' => 'd']], 'c d', 0],

			[[':a', NULL, 5], ':a', 5],
			// #3358
			[[':a', NULL, '3F000'], ':a', '3F000'],
			// #3404
			[[':a', NULL, '42S22'], ':a', '42S22'],
			// #3927
			[[':a', NULL, 'b'], ':a', 'b'],
			// #4039
			[[':a', NULL, '25P01'], ':a', '25P01'],
		];
	}

	/**
	 * Tests Koseven_Koseven_Exception::__construct()
	 *
	 * @test
	 * @dataProvider provider_constructor
	 * @covers Koseven_Koseven_Exception::__construct
	 * @param array             $arguments          Arguments
	 * @param string            $expected_message   Value from getMessage()
	 * @param integer|string    $expected_code      Value from getCode()
	 */
	public function test_constructor($arguments, $expected_message, $expected_code)
	{
		switch (count($arguments))
		{
			case 1:
				$exception = new Koseven_Exception(reset($arguments));
			break;
			case 2:
				$exception = new Koseven_Exception(reset($arguments), next($arguments));
			break;
			default:
				$exception = new Koseven_Exception(reset($arguments), next($arguments), next($arguments));
		}

		$this->assertSame($expected_code, $exception->getCode());
		$this->assertSame($expected_message, $exception->getMessage());
	}

	/**
	 * Provides test data for test_text()
	 *
	 * @return array
	 */
	public function provider_text()
	{
		return [
			[new Koseven_Exception('foobar'), $this->dirSeparator('Koseven_Exception [ 0 ]: foobar ~ SYSPATH/tests/koseven/ExceptionTest.php [ '.__LINE__.' ]')],
		];
	}

	/**
	 * Tests Koseven_Exception::text()
	 *
	 * @test
	 * @dataProvider provider_text
	 * @covers Koseven_Exception::text
	 * @param object $exception exception to test
	 * @param string $expected  expected output
	 */
	public function test_text($exception, $expected)
	{
		$this->assertEquals($expected, Koseven_Exception::text($exception));
	}
}
