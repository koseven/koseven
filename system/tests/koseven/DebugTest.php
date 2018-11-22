<?php

/**
 * Tests Koseven Core
 *
 * @TODO Use a virtual filesystem (see phpunit doc on mocking fs) for find_file etc.
 *
 * @group koseven
 * @group koseven.core
 * @group koseven.core.debug
 *
 * @package    Koseven
 * @category   Tests
 * @author     Jeremy Bush <contractfrombelow@gmail.com>
 * @copyright  (c) 2007-2014  Kohana Team
 * @copyright  (c) 2014-2018  Koseven Team
 * @license    https://koseven.ga/LICENSE.md
 */
class Koseven_DebugTest extends Unittest_TestCase
{

	/**
	 * Provides test data for test_debug()
	 *
	 * @return array
	 */
	public function provider_vars()
	{
		return [
			// $thing, $expected
			[['foobar'], "<pre class=\"debug\"><small>array</small><span>(1)</span> <span>(\n    0 => <small>string</small><span>(6)</span> \"foobar\"\n)</span></pre>"],
		];
	}

	/**
	 * Tests Debug::vars()
	 *
	 * @test
	 * @dataProvider provider_vars
	 * @covers Debug::vars
	 * @param boolean $thing    The thing to debug
	 * @param boolean $expected Output for Debug::vars
	 */
	public function test_var($thing, $expected)
	{
		$this->assertEquals($expected, Debug::vars($thing));
	}

	/**
	 * Provides test data for testDebugPath()
	 *
	 * @return array
	 */
	public function provider_debug_path()
	{
		return [
			[
				SYSPATH.'classes'.DIRECTORY_SEPARATOR. 'Koseven' .EXT,
				'SYSPATH'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'koseven.php'
			],
			[
				MODPATH.$this->dirSeparator('unittest/classes/koseven/unittest/runner').EXT,
				$this->dirSeparator('MODPATH/unittest/classes/koseven/unittest/runner').EXT
			],
		];
	}

	/**
	 * Tests Debug::path()
	 *
	 * @test
	 * @dataProvider provider_debug_path
	 * @covers Debug::path
	 * @param boolean $path     Input for Debug::path
	 * @param boolean $expected Output for Debug::path
	 */
	public function test_debug_path($path, $expected)
	{
		$this->assertEquals($expected, Debug::path($path));
	}

	/**
	 * Provides test data for test_dump()
	 *
	 * @return array
	 */
	public function provider_dump()
	{
		return [
			['foobar', 128, 10, '<small>string</small><span>(6)</span> "foobar"'],
			['foobar', 2, 10, '<small>string</small><span>(6)</span> "fo&nbsp;&hellip;"'],
			[NULL, 128, 10, '<small>NULL</small>'],
			[TRUE, 128, 10, '<small>bool</small> TRUE'],
			[['foobar'], 128, 10, "<small>array</small><span>(1)</span> <span>(\n    0 => <small>string</small><span>(6)</span> \"foobar\"\n)</span>"],
			[new StdClass, 128, 10, "<small>object</small> <span>stdClass(0)</span> <code>{\n}</code>"],
			["fo\x6F\xFF\x00bar\x8F\xC2\xB110", 128, 10, '<small>string</small><span>(10)</span> "foobar±10"'],
			[['level1' => ['level2' => ['level3' => ['level4' => ['value' => 'something']]]]], 128, 4,
'<small>array</small><span>(1)</span> <span>(
    "level1" => <small>array</small><span>(1)</span> <span>(
        "level2" => <small>array</small><span>(1)</span> <span>(
            "level3" => <small>array</small><span>(1)</span> <span>(
                "level4" => <small>array</small><span>(1)</span> (
                    ...
                )
            )</span>
        )</span>
    )</span>
)</span>'],
		];
	}

	/**
	 * Tests Debug::dump()
	 *
	 * @test
	 * @dataProvider provider_dump
	 * @covers Debug::dump
	 * @covers Debug::_dump
	 * @param object $exception exception to test
	 * @param string $expected  expected output
	 */
	public function test_dump($input, $length, $limit, $expected)
	{
		$this->assertEquals($expected, Debug::dump($input, $length, $limit));
	}
}
