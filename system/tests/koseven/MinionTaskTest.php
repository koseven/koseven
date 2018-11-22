<?php

/**
 * Tests the Minion library
 *
 * @group koseven
 * @group koseven.core
 * @group koseven.core.config
 *
 * @package    Koseven
 * @category   Tests
 * @author     Piotr Gołasz <pgolasz@gmail.com>
 * @copyright  (c) 2007-2014  Kohana Team
 * @copyright  (c) 2014-2018  Koseven Team
 * @license    https://koseven.ga/LICENSE.md
 */
class MinionTaskTest extends Unittest_TestCase {

	/**
	 * Tests that Minion Task Help works assuming all other tasks work aswell
	 */
	public function test_minion_runnable()
	{
		$minion_response = Minion_Task::factory(['task' => 'help']);
		$this->assertInstanceOf('Task_Help', $minion_response);
	}
}
