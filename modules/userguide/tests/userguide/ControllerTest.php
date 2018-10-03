<?php

/**
 * Unit tests for internal methods of userguide controller
 *
 * @group kohana
 * @group kohana.userguide
 * @group kohana.userguide.controller
 *
 * @package    Kohana/Userguide
 * @category   Tests
 * @author     Kohana Team
 * @copyright  (c) Kohana Team
 * @license    https://koseven.ga/LICENSE.md
 */
class Userguide_ControllerTest extends Unittest_TestCase
{

	public function provider_file_finds_markdown_files()
	{
		return [
			['userguide/adding', 'guide/userguide/adding.md'],
			['userguide/adding.md', 'guide/userguide/adding.md'],
			['userguide/adding.markdown', 'guide/userguide/adding.md'],
			['userguide/does_not_exist.md', FALSE]
		];
	}

	/**
	 * @dataProvider provider_file_finds_markdown_files
	 * @param  string  $page           Page name passed in the URL
	 * @param  string  $expected_file  Expected result from Controller_Userguide::file
	 */
	public function test_file_finds_markdown_files($page, $expected_file)
	{
		$controller = $this->createMock('Controller_Userguide');

		$cache_reflection = new ReflectionClass('Controller_Userguide');
		$file_method = $cache_reflection->getMethod('file');

		$path = $file_method->invoke($controller, $page);

		// Only verify trailing segments to avoid problems if file overwritten in CFS
		$expected_len = strlen($expected_file);
		$file = substr($path, -$expected_len, $expected_len);

		$this->assertEquals($expected_file, $file);
	}

}
