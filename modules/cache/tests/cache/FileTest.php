<?php
include_once(Kohana::find_file('tests/cache', 'CacheBasicMethodsTest'));

/**
 * @package    Kohana/Cache
 * @group      kohana
 * @group      kohana.cache
 * @category   Test
 * @author     Kohana Team
 * @copyright  (c) Kohana Team
 * @license    https://koseven.ga/LICENSE.md
 */
class Kohana_Cache_FileTest extends Kohana_CacheBasicMethodsTest {

	/**
	 * This method MUST be implemented by each driver to setup the `Cache`
	 * instance for each test.
	 * 
	 * This method should do the following tasks for each driver test:
	 * 
	 *  - Test the Cache instance driver is available, skip test otherwise
	 *  - Setup the Cache instance
	 *  - Call the parent setup method, `parent::setUp()`
	 *
	 * @return  void
	 */
	public function setUp()
	{
		parent::setUp();

		if ( ! Kohana::$config->load('cache.file'))
		{
			Kohana::$config->load('cache')
				->set(
					'file',
					array(
						'driver'             => 'file',
						'cache_dir'          => APPPATH.'cache',
						'default_expire'     => 3600,
						'ignore_on_delete'   => array(
							'file_we_want_to_keep.cache',
							'.gitignore',
							'.git',
							'.svn'
						)
					)
			    );
		}

		$this->cache(Cache::instance('file'));
	}

	/**
	 * Tests that ignored files are not removed from file cache
	 *
	 * @return  void
	 */
	public function test_ignore_delete_file()
	{
		$cache = $this->cache();
		$config = Kohana::$config->load('cache')->file;
		$file = $config['cache_dir'].'/file_we_want_to_keep.cache';

		// Lets pollute the cache folder
		file_put_contents($file, 'foobar');

		$this->assertTrue($cache->delete_all());
		$this->assertTrue(file_exists($file));
		$this->assertEquals('foobar', file_get_contents($file));

		unlink($file);
	}

	/**
	 * Provider for test_utf8
	 *
	 * @return  array
	 */
	public function provider_utf8()
	{
		return array(
			array(
				'This is â ütf-8 Ӝ☃ string',
				'This is â ütf-8 Ӝ☃ string'
			),
			array(
				'㆓㆕㆙㆛',
				'㆓㆕㆙㆛'
			),
			array(
				'அஆஇஈஊ',
				'அஆஇஈஊ'
			)
		);
	}

	/**
	 * Tests the file driver supports utf-8 strings
	 *
	 * @dataProvider provider_utf8
	 *
	 * @return  void
	 */
	public function test_utf8($input, $expected)
	{
		$cache = $this->cache();
		$cache->set('utf8', $input);

		$this->assertSame($expected, $cache->get('utf8'));
	}

	/**
	 * Tests garbage collection.
	 * Tests if non-expired cache files withstand garbage collection
	 *
	 * @test
	 */
	public function test_garbage_collection()
	{
		$cache = $this->cache();
		$cache->set('persistent', 'dummy persistent data', 3);
		$cache->set('volatile', 'dummy volatile data', 1);

		$this->assertTrue($this->is_file('persistent'));
		$this->assertTrue($this->is_file('volatile'));

		// sleep for more than a second
		sleep(2);

		$cache->garbage_collect();

		$this->assertTrue($this->is_file('persistent'));
		$this->assertFalse($this->is_file('volatile'));
	}

	/**
	 * helper method for test_garbage_collection.
	 * Tests if cache file exists given cache id.
	 *
	 * @param string $id cache id
	 * @return boolean TRUE if file exists FALSE otherwise
	 */
	protected function is_file($id)
	{
		$cache = $this->cache();

		$method_sanitize_id = new ReflectionMethod($cache, '_sanitize_id');
		$method_sanitize_id->setAccessible(TRUE);
		$method_filename = new ReflectionMethod($cache, 'filename');
		$method_filename->setAccessible(TRUE);
		$method_resolve_directory = new ReflectionMethod($cache, '_resolve_directory');
		$method_resolve_directory->setAccessible(TRUE);

		$sanitized_id = $method_sanitize_id->invoke($cache, $id);
		$filename = $method_filename->invoke($cache, $sanitized_id);
		$directory = $method_resolve_directory->invoke($cache, $filename);

		$file = new SplFileInfo($directory.$filename);

		//var_dump($cache->_is_expired($file));
		return $file->isFile();
	}
} // End Kohana_SqliteTest
