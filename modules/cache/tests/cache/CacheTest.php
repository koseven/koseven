<?php
/**
 * @package    Kohana/Cache
 * @group      kohana
 * @group      kohana.cache
 * @category   Test
 * @author     Kohana Team
 * @copyright  (c) Kohana Team
 * @license    https://koseven.ga/LICENSE.md
 */
class Kohana_CacheTest extends Unittest_TestCase {

	const BAD_GROUP_DEFINITION  = 1010;
	const EXPECT_SELF           = 1001;

	/**
	 * Data provider for test_instance
	 *
	 * @return  array
	 */
	public function provider_instance()
	{
		$base = [];

		if (Kohana::$config->load('cache.file'))
		{
			$base = [
				// Test default group
				[
					NULL,
					Cache::instance('file')
				],
				// Test defined group
				[
					'file',
					Cache::instance('file')
				],
			];
		}

		return $base + [[
			// Test bad group definition
			Kohana_CacheTest::BAD_GROUP_DEFINITION,
			'Failed to load Kohana Cache group: 1010'
		]];
	}

	/**
	 * Tests the [Cache::factory()] method behaves as expected
	 * 
	 * @dataProvider provider_instance
	 *
	 * @return  void
	 */
	public function test_instance($group, $expected)
	{
		try
		{
			$cache = Cache::instance($group);
		}
		catch (Cache_Exception $e)
		{
			$this->assertSame($expected, $e->getMessage());
			return;
		}

		$this->assertInstanceOf(get_class($expected), $cache);
		$this->assertSame($expected->config(), $cache->config());
	}

	/**
	 * Tests that `clone($cache)` will be prevented to maintain singleton
	 *
	 * @return  void
	 * @expectedException Cache_Exception
	 */
	public function test_cloning_fails()
	{
		$cache = $this->getMockBuilder('Cache')
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		try
		{
			clone($cache);
		}
		catch (Cache_Exception $e)
		{
			$this->assertSame('Cloning of Kohana_Cache objects is forbidden', 
				$e->getMessage());
			throw $e;
		}
	}

	/**
	 * Data provider for test_config
	 *
	 * @return  array
	 */
	public function provider_config()
	{
		return [
			[
				[
					'server'     => 'otherhost',
					'port'       => 5555,
					'persistent' => TRUE,
				],
				NULL,
				Kohana_CacheTest::EXPECT_SELF,
				[
					'server'     => 'otherhost',
					'port'       => 5555,
					'persistent' => TRUE,
				],
			],
			[
				'foo',
				'bar',
				Kohana_CacheTest::EXPECT_SELF,
				[
					'foo'        => 'bar'
				]
			],
			[
				'server',
				NULL,
				NULL,
				[]
			],
			[
				NULL,
				NULL,
				[],
				[]
			]
		];
	}

	/**
	 * Tests the config method behaviour
	 * 
	 * @dataProvider provider_config
	 *
	 * @param   mixed    key value to set or get
	 * @param   mixed    value to set to key
	 * @param   mixed    expected result from [Cache::config()]
	 * @param   array    expected config within cache
	 * @return  void
	 */
	public function test_config($key, $value, $expected_result, array $expected_config)
	{
		$cache = $this->createMock('Cache_File');

		$cache_reflection = new ReflectionClass('Cache_File');
		$config = $cache_reflection->getMethod('config');

		if ($expected_result === Kohana_CacheTest::EXPECT_SELF)
		{
			$expected_result = $cache;
		}

		$this->assertSame($expected_result, $config->invoke($cache, $key, $value));
		$this->assertSame($expected_config, $config->invoke($cache));
	}

	/**
	 * Data provider for test_sanitize_id
	 *
	 * @return  array
	 */
	public function provider_sanitize_id()
	{
		return [
			[
				'foo',
				'0beec7b5ea3f0fdbc95d0dd47f3c5bc275da8a33'
			],
		];
	}

	/**
	 * Tests the [Cache::_sanitize_id()] method works as expected.
	 * This uses some nasty reflection techniques to access a protected
	 * method.
	 * 
	 * @dataProvider provider_sanitize_id
	 *
	 * @param   string    id 
	 * @param   string    expected 
	 * @return  void
	 */
	public function test_sanitize_id($id, $expected)
	{
		$cache = $this->createMock('Cache');

		$cache_reflection = new ReflectionClass('Cache');
		$sanitize_id = $cache_reflection->getMethod('_sanitize_id');
		$sanitize_id->setAccessible(TRUE);

		$this->assertSame($expected, $sanitize_id->invoke($cache, $id));
	}
} // End Kohana_CacheTest
