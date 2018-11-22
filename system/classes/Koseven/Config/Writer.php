<?php
/**
 * Interface for config writers
 *
 * Specifies the methods that a config writer must implement
 *
 * @package Koseven
 * @copyright  (c) 2007-2014  Kohana Team
 * @copyright  (c) 2014-2018  Koseven Team
 * @license    https://koseven.ga/LICENSE.md
 */
interface Koseven_Config_Writer extends Koseven_Config_Source
{
	/**
	 * Writes the passed config for $group
	 *
	 * Returns chainable instance on success or throws
	 * Koseven_Config_Exception on failure
	 *
	 * @param string      $group  The config group
	 * @param string      $key    The config key to write to
	 * @param array       $config The configuration to write
	 * @return boolean
	 */
	public function write($group, $key, $config);

}
