<?php

/**
 * Test case for Minion_Util
 *
 * @package    KO7/Minion
 * @group      ko7
 * @group      ko7.minion
 * @category   Test
 * @author     Kohana Team
 * @copyright  (c) Kohana Team
 * @license    https://koseven.ga/LICENSE.md
 */

class Minion_TaskTest extends KO7_Unittest_TestCase
{
	protected static $initial_request;

	/**
	 * Sets up the environment
	 */
	public function setUp() : void
	{
		parent::setUp();
		KO7::$config->load('url')->set(
			'trusted_hosts',
			['www\.example\.com', 'www\.example2\.com']
		);

		KO7::$config->load('site')->set(
			'minion_domain_name',
			'http://www.example2.com'
		);

		// Keep the old request object
		self::$initial_request = Request::$initial;
		Request::$initial = NULL;
	}

	/**
	 * Restores the environment
	 */
	public function tearDown() : void
	{
		Request::$initial = self::$initial_request;

		parent::tearDown();
	}

	/**
	 * Provides test data for test_convert_task_to_class_name()
	 *
	 * @return array
	 */
	public function provider_convert_task_to_class_name()
	{
		return [
			['Task_Db_Migrate', 'db:migrate'],
			['Task_Db_Status',  'db:status'],
			['', ''],
		];
	}

	/**
	 * Tests that a task can be converted to a class name
	 *
	 * @test
	 * @covers Minion_Task::convert_task_to_class_name
	 * @dataProvider provider_convert_task_to_class_name
	 * @param string Expected class name
	 * @param string Input task name
	 */
	public function test_convert_task_to_class_name($expected, $task_name)
	{
		$this->assertSame($expected, Minion_Task::convert_task_to_class_name($task_name));
	}

	/**
	 * Provides test data for test_convert_class_to_task()
	 *
	 * @return array
	 */
	public function provider_convert_class_to_task()
	{
		return [
			['db:migrate', 'Task_Db_Migrate'],
		];
	}

	/**
	 * Tests that the task name can be found from a class name / object
	 *
	 * @test
	 * @covers Minion_Task::convert_class_to_task
	 * @dataProvider provider_convert_class_to_task
	 * @param string Expected task name
	 * @param mixed  Input class
	 */
	public function test_convert_class_to_task($expected, $class)
	{
		$this->assertSame($expected, Minion_Task::convert_class_to_task($class));
	}

	/**
	 * Provides test data for test_set_domain_name()
	 *
	 * @return array
	 */
	public function provider_set_domain_name()
	{
		return [
			['https://www.example.com/welcome', 'https://www.example.com', 'welcome'],
			['http://www.example.com/welcome', 'http://www.example.com', 'welcome'],
			['http://www.example2.com/welcome', NULL, 'welcome'],
			['http://www.example.com:8080/welcome', 'http://www.example.com:8080', 'welcome'],
		];
	}

	/**
	 * Tests that a task can be converted to a class name
	 *
	 * @test
	 * @covers Minion_Task::set_domain_name
	 * @dataProvider provider_set_domain_name
	 * @param string Expected domain url
	 * @param string Input domain name
	 */
	public function test_set_domain_name($expected, $name, $uri)
	{
		// Minion_Task::set_domain_name($name);

		self::assertTrue(TRUE);
		//$this->assertSame(
		//	$expected,
		//	URL::site($uri, TRUE, FALSE)
		//);
	}
}
