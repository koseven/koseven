<?php

/**
 * This test only really exists for code coverage.
 *
 * @group koseven
 * @group koseven.core
 * @group koseven.core.model
 *
 * @package    Koseven
 * @category   Tests
 * @author     BRMatt <matthew@sigswitch.com>
 * @copyright  (c) 2007-2014  Kohana Team
 * @copyright  (c) 2014-2018  Koseven Team
 * @license    https://koseven.ga/LICENSE.md
 */
class Koseven_ModelTest extends Unittest_TestCase
{
	/**
	 * Test the model's factory.
	 *
	 * @test
	 * @covers Model::factory
	 */
	public function test_create()
	{
		$foobar = Model::factory('Foobar');

		$this->assertEquals(TRUE, $foobar instanceof Model);
	}
}

class Model_Foobar extends Model
{

}
