<?php

/**
 * Tests the Config file reader that's shipped with ko7
 *
 * @group ko7
 * @group ko7.config
 *
 * @package    Unittest
 * @author     Kohana Team
 * @author     Jeremy Bush <contractfrombelow@gmail.com>
 * @author     Matt Button <matthew@sigswitch.com>
 * @copyright  (c) Kohana Team
 * @license    https://koseven.ga/LICENSE.md
 */
class KO7_Config_File_ReaderTest extends KO7_Unittest_TestCase {

	/**
	 * If we don't pass a directory to the reader then it should assume
	 * that we want to search the dir 'config' by default
	 *
	 * @test
	 * @covers KO7_Config_File_Reader
	 */
	public function test_default_search_dir_is_config()
	{
		$reader = new KO7_Config_File_Reader;

		$this->assertAttributeSame('config', '_directory', $reader);
	}

	/**
	 * If we pass a directory to the constructor of the file reader it
	 * should change the search directory
	 *
	 * @test
	 * @covers KO7_Config_File_Reader
	 */
	public function test_constructor_sets_search_dir_from_param()
	{
		$reader = new KO7_Config_File_Reader('gafloog');

		$this->assertAttributeSame('gafloog', '_directory', $reader);
	}

	/**
	 * If the config dir does not exist then the function should just
	 * return an empty array
	 *
	 * @test
	 * @covers KO7_Config_File_Reader::load
	 */
	public function test_load_returns_empty_array_if_conf_dir_dnx()
	{
		$config = new KO7_Config_File_Reader('gafloogle');

		$this->assertSame([], $config->load('values'));
	}

	/**
	 * If the requested config group does not exist then the reader
	 * should return an empty array
	 *
	 * @test
	 * @covers KO7_Config_File_Reader::load
	 */
	public function test_load_returns_empty_array_if_conf_dnx()
	{
		$config = new KO7_Config_File_Reader;

		$this->assertSame([], $config->load('gafloogle'));
	}

	/**
	 * Test that the load() function is actually loading the
	 * configuration from the files.
	 *
	 * @test
	 * @covers KO7_Config_File_Reader::load
	 */
	public function test_loads_config_from_files()
	{
		$config = new KO7_Config_File_Reader;

		$values = $config->load('inflector');

		// Due to the way the cascading filesystem works there could be
		// any number of modifications to the system config in the
		// actual output.  Therefore to increase compatability we just
		// check that we've got an array and that it's not empty
		$this->assertNotSame([], $values);
		$this->assertInternalType('array',    $values);
	}
}