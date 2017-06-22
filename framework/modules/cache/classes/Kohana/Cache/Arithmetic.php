<?php
/**
 * Kohana Cache Arithmetic Interface, for basic cache integer based
 * arithmetic, addition and subtraction
 * 
 * @package    Kohana/Cache
 * @category   Base
 * @author     Kohana Team
 * @copyright  (c) Kohana Team
 * @license    https://koseven.ga/LICENSE.md
 * @since      3.2.0
 */
interface Kohana_Cache_Arithmetic {

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
	public function increment($id, $step = 1);

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
	public function decrement($id, $step = 1);

} // End Kohana_Cache_Arithmetic