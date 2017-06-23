<?php
/**
 * [Kohana Cache](api/Kohana_Cache) APCu data store driver for Kohana Cache
 * library.
 *
 * ### Configuration example
 *
 * Below is an example of an _apcu_ server configuration.
 *
 *     return array(
 *          'apcu' => array(                          // Driver group
 *                  'driver'         => 'apcu',         // using APCu driver
 *           ),
 *     )
 *
 * In cases where only one cache group is required, if the group is named `default` there is
 * no need to pass the group name when instantiating a cache instance.
 *
 * #### General cache group configuration settings
 *
 * Below are the settings available to all types of cache driver.
 *
 * Name           | Required | Description
 * -------------- | -------- | ---------------------------------------------------------------
 * driver         | __YES__  | (_string_) The driver type to use
 *
 * ### System requirements
 *
 * *  Kohana 3.0.x
 * *  PHP 5.2.4 or greater
 * *  APCu PHP extension
 *
 * @package    Kohana/Cache
 * @category   Base
 * @author     Kohana Team
 * @copyright  (c) Kohana Team
 * @license    https://koseven.ga/LICENSE.md
 */
class Kohana_Cache_Apcu extends Cache implements Cache_Arithmetic {

	/**
	 * Check for existence of the APCu extension This method cannot be invoked externally. The driver must
	 * be instantiated using the `Cache::instance()` method.
	 *
	 * @param  array  $config  configuration
	 * @throws Cache_Exception
	 */
	protected function __construct(array $config)
	{
		if ( ! extension_loaded('apcu'))
		{
			throw new Cache_Exception('PHP APCu extension is not available.');
		}

		parent::__construct($config);
	}

	/**
	 * Retrieve a cached value entry by id.
	 *
	 *     // Retrieve cache entry from apcu group
	 *     $data = Cache::instance('apcu')->get('foo');
	 *
	 *     // Retrieve cache entry from apcu group and return 'bar' if miss
	 *     $data = Cache::instance('apcu')->get('foo', 'bar');
	 *
	 * @param   string  $id       id of cache to entry
	 * @param   string  $default  default value to return if cache miss
	 * @return  mixed
	 * @throws  Cache_Exception
	 */
	public function get($id, $default = NULL)
	{
		$data = apcu_fetch($this->_sanitize_id($id), $success);

		return $success ? $data : $default;
	}

	/**
	 * Set a value to cache with id and lifetime
	 *
	 *     $data = 'bar';
	 *
	 *     // Set 'bar' to 'foo' in apcu group, using default expiry
	 *     Cache::instance('apcu')->set('foo', $data);
	 *
	 *     // Set 'bar' to 'foo' in apcu group for 30 seconds
	 *     Cache::instance('apcu')->set('foo', $data, 30);
	 *
	 * @param   string   $id        id of cache entry
	 * @param   string   $data      data to set to cache
	 * @param   integer  $lifetime  lifetime in seconds
	 * @return  boolean
	 */
	public function set($id, $data, $lifetime = NULL)
	{
		if ($lifetime === NULL)
		{
			$lifetime = Arr::get($this->_config, 'default_expire', Cache::DEFAULT_EXPIRE);
		}

		return apcu_store($this->_sanitize_id($id), $data, $lifetime);
	}

	/**
	 * Delete a cache entry based on id
	 *
	 *     // Delete 'foo' entry from the apcu group
	 *     Cache::instance('apcu')->delete('foo');
	 *
	 * @param   string  $id  id to remove from cache
	 * @return  boolean
	 */
	public function delete($id)
	{
		return apcu_delete($this->_sanitize_id($id));
	}

	/**
	 * Delete all cache entries.
	 *
	 * Beware of using this method when
	 * using shared memory cache systems, as it will wipe every
	 * entry within the system for all clients.
	 *
	 *     // Delete all cache entries in the apcu group
	 *     Cache::instance('apcu')->delete_all();
	 *
	 * @return  boolean
	 */
	public function delete_all()
	{
		return apcu_clear_cache();
	}

	/**
	 * Increments a given value by the step value supplied.
	 * Useful for shared counters and other persistent integer based
	 * tracking.
	 *
	 * @param   string    id of cache entry to increment
	 * @param   int       step value to increment by
	 * @return  integer
	 * @return  boolean
	 */
	public function increment($id, $step = 1)
	{
		if (apcu_exists($id)) {
			return apcu_inc($id, $step);
		} else {
			return FALSE;
		}
	}

	/**
	 * Decrements a given value by the step value supplied.
	 * Useful for shared counters and other persistent integer based
	 * tracking.
	 *
	 * @param   string    id of cache entry to decrement
	 * @param   int       step value to decrement by
	 * @return  integer
	 * @return  boolean
	 */
	public function decrement($id, $step = 1)
	{
		if (apcu_exists($id)) {
			return apcu_dec($id, $step);
		} else {
			return FALSE;
		}
	}

} // End Kohana_Cache_Apcu
