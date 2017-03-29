<?php
if (isset($_ENV['TRAVIS']))
{
	// This is really hacky, but without it the result is permanently full of noise that makes it impossible to see
	// any unexpected skipped tests.
	print "Skipping all Wincache driver tests as these will never run on Travis.".\PHP_EOL;
	return;
}
else
{
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
	class Kohana_WincacheTest extends Kohana_CacheBasicMethodsTest {

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

			if ( ! extension_loaded('wincache'))
			{
				$this->markTestSkipped('Wincache PHP Extension is not available');
			}

			$this->cache(Cache::instance('wincache'));
		}

	} // End Kohana_WincacheTest
}
