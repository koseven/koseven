<?php

/**
 * Interface for config readers
 *
 * @package    Kohana
 * @category   Configuration
 * @author     Kohana Team
 * @copyright  (c) Kohana Team
 * @license    https://koseven.ga/LICENSE.md
 */
interface Kohana_Config_Reader extends Kohana_Config_Source
{

	/**
	 * Tries to load the specified configuration group
	 *
	 * Returns FALSE if group does not exist or an array if it does
	 *
	 * @param  string $group Configuration group
	 * @return boolean|array
	 */
	public function load($group);

}
