<?php

/**
 * Tests the Arr lib that's shipped with kohana
 *
 * @group kohana
 * @group kohana.core
 * @group kohana.core.arr
 *
 * @package    Koseven
 * @category   Tests
 * @author     Kohana Team
 * @author     BRMatt <matthew@sigswitch.com>
 * @copyright  (c) Kohana Team
 * @license    https://koseven.ga/LICENSE.md
 */
class Kohana_ArrTest extends Unittest_TestCase
{
	/**
	 * Provides test data for test_callback()
	 *
	 * @return array
	 */
	public function provider_callback()
	{
		return [
			// Tests....
			// That no parameters returns null
			['function', ['function', NULL]],
			// That we can get an array of parameters values
			['function(1,2,3)', ['function', ['1', '2', '3']]],
			// That it's not just using the callback "function"
			['different_name(harry,jerry)', ['different_name', ['harry', 'jerry']]],
			// That static callbacks are parsed into arrays
			['kohana::appify(this)', [['Koseven', 'appify'], ['this']]],
			// Spaces are preserved in parameters
			['deal::make(me, my mate )', [['deal', 'make'], ['me', ' my mate ']]]
			// TODO: add more cases
		];
	}

	/**
	 * Tests Arr::callback()
	 *
	 * @test
	 * @dataProvider provider_callback
	 * @param string $str       String to parse
	 * @param array  $expected  Callback and its parameters
	 */
	public function test_callback($str, $expected)
	{
		$result = Arr::callback($str);

		$this->assertSame(2, count($result));
		$this->assertSame($expected, $result);
	}

	/**
	 * Provides test data for test_extract
	 *
	 * @return array
	 */
	public function provider_extract()
	{
		return [
			[
				['Koseven' => 'awesome', 'blueflame' => 'was'],
				['Koseven', 'cakephp', 'symfony'],
				NULL,
				['Koseven' => 'awesome', 'cakephp' => NULL, 'symfony' => NULL]
			],
			// I realise noone should EVER code like this in real life,
			// but unit testing is very very very very boring
			[
				['chocolate cake' => 'in stock', 'carrot cake' => 'in stock'],
				['carrot cake', 'humble pie'],
				'not in stock',
				['carrot cake' => 'in stock', 'humble pie' => 'not in stock'],
			],
			[
				// Source Array
				['level1' => ['level2a' => 'value 1', 'level2b' => 'value 2']],
				// Paths to extract
				['level1.level2a', 'level1.level2b'],
				// Default
				NULL,
				// Expected Result
				['level1' => ['level2a' => 'value 1', 'level2b' => 'value 2']],
			],
			[
				// Source Array
				['level1a' => ['level2a' => 'value 1'], 'level1b' => ['level2b' => 'value 2']],
				// Paths to extract
				['level1a', 'level1b.level2b'],
				// Default
				NULL,
				// Expected Result
				['level1a' => ['level2a' => 'value 1'], 'level1b' => ['level2b' => 'value 2']],
			],
			[
				// Source Array
				['level1a' => ['level2a' => 'value 1'], 'level1b' => ['level2b' => 'value 2']],
				// Paths to extract
				['level1a', 'level1b.level2b', 'level1c', 'level1d.notfound'],
				// Default
				'default',
				// Expected Result
				['level1a' => ['level2a' => 'value 1'], 'level1b' => ['level2b' => 'value 2'], 'level1c' => 'default', 'level1d' => ['notfound' => 'default']],
			],
		];
	}

	/**
	 * Tests Arr::extract()
	 *
	 * @test
	 * @dataProvider provider_extract
	 * @param array $array
	 * @param array $paths
	 * @param mixed $default
	 * @param array $expected
	 */
	public function test_extract(array $array, array $paths, $default, $expected)
	{
		$array = Arr::extract($array, $paths, $default);

		$this->assertSame(count($expected), count($array));
		$this->assertSame($expected, $array);
	}

	/**
	 * Provides test data for test_pluck
	 *
	 * @return array
	 */
	public function provider_pluck()
	{
		return [
			[
				[
					  ['id' => 20, 'name' => 'John Smith'],
					  ['name' => 'Linda'],
					  ['id' => 25, 'name' => 'Fred'],
					 ],
				'id',
				[20, 25]
			],
		];
	}

	/**
	 * Tests Arr::pluck()
	 *
	 * @test
	 * @dataProvider provider_pluck
	 * @param array $array
	 * @param string $key
	 * @param array $expected
	 */
	public function test_pluck(array $array, $key, $expected)
	{
		$array = Arr::pluck($array, $key);

		$this->assertSame(count($expected), count($array));
		$this->assertSame($expected, $array);
	}

	/**
	 * Provides test data for test_get()
	 *
	 * @return array
	 */
	public function provider_get()
	{
		return [
			[['uno', 'dos', 'tress'], 1, NULL, 'dos'],
			[['we' => 'can', 'make' => 'change'], 'we', NULL, 'can'],

			[['uno', 'dos', 'tress'], 10, NULL, NULL],
			[['we' => 'can', 'make' => 'change'], 'he', NULL, NULL],
			[['we' => 'can', 'make' => 'change'], 'he', 'who', 'who'],
			[['we' => 'can', 'make' => 'change'], 'he', ['arrays'], ['arrays']],
		];
	}

	/**
	 * Tests Arr::get()
	 *
	 * @test
	 * @dataProvider provider_get()
	 * @param array          $array      Array to look in
	 * @param string|integer $key        Key to look for
	 * @param mixed          $default    What to return if $key isn't set
	 * @param mixed          $expected   The expected value returned
	 */
	public function test_get(array $array, $key, $default, $expected)
	{
		$this->assertSame(
			$expected,
			Arr::get($array, $key, $default)
		);
	}

	/**
	 * Provides test data for test_is_assoc()
	 *
	 * @return array
	 */
	public function provider_is_assoc()
	{
		return [
			[['one', 'two', 'three'], FALSE],
			[['one' => 'o clock', 'two' => 'o clock', 'three' => 'o clock'], TRUE],
		];
	}

	/**
	 * Tests Arr::is_assoc()
	 *
	 * @test
	 * @dataProvider provider_is_assoc
	 * @param array   $array     Array to check
	 * @param boolean $expected  Is $array assoc
	 */
	public function test_is_assoc(array $array, $expected)
	{
		$this->assertSame(
			$expected,
			Arr::is_assoc($array)
		);
	}

	/**
	 * Provides test data for test_is_array()
	 *
	 * @return array
	 */
	public function provider_is_array()
	{
		return [
			[$a = ['one', 'two', 'three'], TRUE],
			[new ArrayObject($a), TRUE],
			[new ArrayIterator($a), TRUE],
			['not an array', FALSE],
			[new stdClass, FALSE],
		];
	}

	/**
	 * Tests Arr::is_array()
	 *
	 * @test
	 * @dataProvider provider_is_array
	 * @param mixed   $value     Value to check
	 * @param boolean $expected  Is $value an array?
	 */
	public function test_is_array($array, $expected)
	{
		$this->assertSame(
			$expected,
			Arr::is_array($array)
		);
	}

	public function provider_merge()
	{
		return [
			// Test how it merges arrays and sub arrays with assoc keys
			[
				['name' => 'mary', 'children' => ['fred', 'paul', 'sally', 'jane']],
				['name' => 'john', 'children' => ['fred', 'paul', 'sally', 'jane']],
				['name' => 'mary', 'children' => ['jane']],
			],
			// See how it merges sub-arrays with numerical indexes
			[
				[['test1'], ['test2'], ['test3']],
				[['test1'], ['test2']],
				[['test2'], ['test3']],
			],
			[
				[[['test1']], [['test2']], [['test3']]],
				[[['test1']], [['test2']]],
				[[['test2']], [['test3']]],
			],
			[
				['a' => ['test1','test2'], 'b' => ['test2','test3']],
				['a' => ['test1'], 'b' => ['test2']],
				['a' => ['test2'], 'b' => ['test3']],
			],
			[
				['digits' => [0, 1, 2, 3]],
				['digits' => [0, 1]],
				['digits' => [2, 3]],
			],
			// See how it manages merging items with numerical indexes
			[
				[0, 1, 2, 3],
				[0, 1],
				[2, 3],
			],
			// Try and get it to merge assoc. arrays recursively
			[
				['foo' => 'bar', ['temp' => 'life']],
				['foo' => 'bin', ['temp' => 'name']],
				['foo' => 'bar', ['temp' => 'life']],
			],
			// Bug #3139
			[
				['foo'	=> ['bar']],
				['foo'	=> 'bar'],
				['foo'	=> ['bar']],
			],
			[
				['foo'	=> 'bar'],
				['foo'	=> ['bar']],
				['foo'	=> 'bar'],
			],

			// data set #9
			// Associative, Associative
			[
				['a' => 'K', 'b' => 'K', 'c' => 'L'],
				['a' => 'J', 'b' => 'K'],
				['a' => 'K', 'c' => 'L'],
			],
			// Associative, Indexed
			[
				['a' => 'J', 'b' => 'K', 'L'],
				['a' => 'J', 'b' => 'K'],
				['K', 'L'],
			],
			// Associative, Mixed
			[
				['a' => 'J', 'b' => 'K', 'K', 'c' => 'L'],
				['a' => 'J', 'b' => 'K'],
				['K', 'c' => 'L'],
			],

			// data set #12
			// Indexed, Associative
			[
				['J', 'K', 'a' => 'K', 'c' => 'L'],
				['J', 'K'],
				['a' => 'K', 'c' => 'L'],
			],
			// Indexed, Indexed
			[
				['J', 'K', 'L'],
				['J', 'K'],
				['K', 'L'],
			],
			// Indexed, Mixed
			[
				['K', 'K', 'c' => 'L'],
				['J', 'K'],
				['K', 'c' => 'L'],
			],

			// data set #15
			// Mixed, Associative
			[
				['a' => 'K', 'K', 'c' => 'L'],
				['a' => 'J', 'K'],
				['a' => 'K', 'c' => 'L'],
			],
			// Mixed, Indexed
			[
				['a' => 'J', 'K', 'L'],
				['a' => 'J', 'K'],
				['J', 'L'],
			],
			// Mixed, Mixed
			[
				['a' => 'K', 'L'],
				['a' => 'J', 'K'],
				['a' => 'K', 'L'],
			],

			// Bug #3141
			[
				['servers' => [['1.1.1.1', 4730], ['2.2.2.2', 4730]]],
				['servers' => [['1.1.1.1', 4730]]],
				['servers' => [['2.2.2.2', 4730]]],
			],
		];
	}

	/**
	 *
	 * @test
	 * @dataProvider provider_merge
	 */
	public function test_merge($expected, $array1, $array2)
	{
		$this->assertSame(
			$expected,
			Arr::merge($array1,$array2)
		);
	}

	/**
	 * Provides test data for test_path()
	 *
	 * @return array
	 */
	public function provider_path()
	{
		$array = [
			'foobar' => ['definition' => 'lost'],
			'Koseven' => 'awesome',
			'users'  => [
				1 => ['name' => 'matt'],
				2 => ['name' => 'john', 'interests' => ['hocky' => ['length' => 2], 'football' => []]],
				3 => 'frank', // Issue #3194
			],
			'object' => new ArrayObject(['iterator' => TRUE]), // Iterable object should work exactly the same
		];

		return [
			// Tests returns normal values
			[$array['foobar'], $array, 'foobar'],
			[$array['Koseven'], $array, 'Koseven'],
			[$array['foobar']['definition'], $array, 'foobar.definition'],
			// Custom delimiters
			[$array['foobar']['definition'], $array, 'foobar/definition', NULL, '/'],
			// We should be able to use NULL as a default, returned if the key DNX
			[NULL, $array, 'foobar.alternatives',  NULL],
			[NULL, $array, 'kohana.alternatives',  NULL],
			// Try using a string as a default
			['nothing', $array, 'kohana.alternatives',  'nothing'],
			// Make sure you can use arrays as defaults
			[['far', 'wide'], $array, 'cheese.origins',  ['far', 'wide']],
			// Ensures path() casts ints to actual integers for keys
			[$array['users'][1]['name'], $array, 'users.1.name'],
			// Test that a wildcard returns the entire array at that "level"
			[$array['users'], $array, 'users.*'],
			// Now we check that keys after a wilcard will be processed
			[[0 => [0 => 2]], $array, 'users.*.interests.*.length'],
			// See what happens when it can't dig any deeper from a wildcard
			[NULL, $array, 'users.*.fans'],
			// Starting wildcards, issue #3269
			[['matt', 'john'], $array['users'], '*.name'],
			// Path as array, issue #3260
			[$array['users'][2]['name'], $array, ['users', 2, 'name']],
			[$array['object']['iterator'], $array, 'object.iterator'],
		];
	}

	/**
	 * Tests Arr::path()
	 *
	 * @test
	 * @dataProvider provider_path
	 * @param string  $path       The path to follow
	 * @param mixed   $default    The value to return if dnx
	 * @param boolean $expected   The expected value
	 * @param string  $delimiter  The path delimiter
	 */
	public function test_path($expected, $array, $path, $default = NULL, $delimiter = NULL)
	{
		$this->assertSame(
			$expected,
			Arr::path($array, $path, $default, $delimiter)
		);
	}

	/**
	 * Provides test data for test_path()
	 *
	 * @return array
	 */
	public function provider_set_path()
	{
		return [
			// Tests returns normal values
			[['foo' => 'bar'], [], 'foo', 'bar'],
			[['Koseven' => ['is' => 'awesome']], [], 'kohana.is', 'awesome'],
			[['Koseven' => ['is' => 'cool', 'and' => 'slow']],
				  ['Koseven' => ['is' => 'cool']], 'kohana.and', 'slow'],
			// Custom delimiters
			[['Koseven' => ['is' => 'awesome']], [], 'kohana/is', 'awesome', '/'],
			// Ensures set_path() casts ints to actual integers for keys
			[['foo' => ['bar']], ['foo' => ['test']], 'foo.0', 'bar'],
			// Tests if it allows arrays
			[['Koseven' => ['is' => 'awesome']], [], ['Koseven', 'is'], 'awesome'],
		];
	}

	/**
	 * Tests Arr::path()
	 *
	 * @test
	 * @dataProvider provider_set_path
	 * @param string  $path       The path to follow
	 * @param boolean $expected   The expected value
	 * @param string  $delimiter  The path delimiter
	 */
	public function test_set_path($expected, $array, $path, $value, $delimiter = NULL)
	{
		Arr::set_path($array, $path, $value, $delimiter);

		$this->assertSame($expected, $array);
	}

	/**
	 * Provides test data for test_range()
	 *
	 * @return array
	 */
	public function provider_range()
	{
		return [
			[1, 2],
			[1, 100],
			[25, 10],
		];
	}

	/**
	 * Tests Arr::range()
	 *
	 * @dataProvider provider_range
	 * @param integer $step  The step between each value in the array
	 * @param integer $max   The max value of the range (inclusive)
	 */
	public function test_range($step, $max)
	{
		$range = Arr::range($step, $max);

		$this->assertSame( (int) floor($max / $step), count($range));

		$current = $step;

		foreach ($range as $key => $value)
		{
			$this->assertSame($key, $value);
			$this->assertSame($current, $key);
			$this->assertLessThanOrEqual($max, $key);
			$current += $step;
		}
	}

	/**
	 * Provides test data for test_unshift()
	 *
	 * @return array
	 */
	public function provider_unshift()
	{
		return [
			[['one' => '1', 'two' => '2',], 'zero', '0'],
			[['step 1', 'step 2', 'step 3'], 'step 0', 'wow']
		];
	}

	/**
	 * Tests Arr::unshift()
	 *
	 * @test
	 * @dataProvider provider_unshift
	 * @param array $array
	 * @param string $key
	 * @param mixed $value
	 */
	public function test_unshift(array $array, $key, $value)
	{
		$original = $array;

		Arr::unshift($array, $key, $value);

		$this->assertNotSame($original, $array);
		$this->assertSame(count($original) + 1, count($array));
		$this->assertArrayHasKey($key, $array);

		$this->assertSame($value, reset($array));
		$this->assertSame(key($array), $key);
	}

	/**
	 * Provies test data for test_overwrite
	 *
	 * @return array Test Data
	 */
	public function provider_overwrite()
	{
		return [
			[
				['name' => 'Henry', 'mood' => 'tired', 'food' => 'waffles', 'sport' => 'checkers'],
				['name' => 'John', 'mood' => 'bored', 'food' => 'bacon', 'sport' => 'checkers'],
				['name' => 'Matt', 'mood' => 'tired', 'food' => 'waffles'],
				['name' => 'Henry', 'age' => 18,],
			],
		];
	}

	/**
	 *
	 * @test
	 * @dataProvider provider_overwrite
	 */
	public function test_overwrite($expected, $arr1, $arr2, $arr3 = [], $arr4 = [])
	{
		$this->assertSame(
			$expected,
			Arr::overwrite($arr1, $arr2, $arr3, $arr4)
		);
	}

	/**
	 * Provides test data for test_map
	 *
	 * @return array Test Data
	 */
	public function provider_map()
	{
		return [
			['strip_tags', ['<p>foobar</p>'], NULL, ['foobar']],
			['strip_tags', [['<p>foobar</p>'], ['<p>foobar</p>']], NULL, [['foobar'], ['foobar']]],
			[
				'strip_tags',
				[
					'foo' => '<p>foobar</p>',
					'bar' => '<p>foobar</p>',
				],
				NULL,
				[
					'foo' => 'foobar',
					'bar' => 'foobar',
				],
			],
			[
				'strip_tags',
				[
					'foo' => '<p>foobar</p>',
					'bar' => '<p>foobar</p>',
				],
				['foo'],
				[
					'foo' => 'foobar',
					'bar' => '<p>foobar</p>',
				],
			],
			[
				[
					'strip_tags',
					'trim',
				],
				[
					'foo' => '<p>foobar </p>',
					'bar' => '<p>foobar</p>',
				],
				NULL,
				[
					'foo' => 'foobar',
					'bar' => 'foobar',
				],
			],
			[
				'strip_tags',
				[
					[
						'foo' => '<p>foobar</p>',
						'bar' => '<p>foobar</p>',
					],
				],
				['foo'],
				[
					[
						'foo' => 'foobar',
						'bar' => '<p>foobar</p>',
					],
				],
			],
		];
	}

	/**
	 *
	 * @test
	 * @dataProvider provider_map
	 */
	public function test_map($method, $source, $keys, $expected)
	{
		$this->assertSame(
			$expected,
			Arr::map($method, $source, $keys)
		);
	}

	/**
	 * Provides test data for test_flatten
	 *
	 * @return array Test Data
	 */
	public function provider_flatten()
	{
		return [
			[['set' => ['one' => 'something'], 'two' => 'other'], ['one' => 'something', 'two' => 'other']],
		];
	}

	/**
	 *
	 * @test
	 * @dataProvider provider_flatten
	 */
	public function test_flatten($source, $expected)
	{
		$this->assertSame(
			$expected,
			Arr::flatten($source)
		);
	}
}
