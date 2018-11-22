<?php
/**
 * File-based configuration reader. Multiple configuration directories can be
 * used by attaching multiple instances of this class to [Kohana_Config].
 *
 * @package    Koseven
 * @category   Configuration
 * @copyright  (c) 2007-2014  Kohana Team
 * @copyright  (c) 2014-2018  Koseven Team
 * @license    https://koseven.ga/LICENSE.md
 */
class Koseven_Config_File_Reader implements Koseven_Config_Reader {

	/**
	 * The directory where config files are located
	 * @var string
	 */
	protected $_directory = '';

	/**
	 * Creates a new file reader using the given directory as a config source
	 *
	 * @param string    $directory  Configuration directory to search
	 */
	public function __construct($directory = 'config')
	{
		// Set the configuration directory name
		$this->_directory = trim($directory, '/');
	}

	/**
	 * Load and merge all of the configuration files in this group.
	 *
	 *     $config->load($name);
	 *
	 * @param   string  $group  configuration group name
	 * @return  $this   current object
	 * @uses    Koseven::load
	 */
	public function load($group)
	{
		$config = [];

		if ($files = Koseven::find_file($this->_directory, $group, NULL, TRUE))
		{
			foreach ($files as $file)
			{
				// Merge each file to the configuration array
				$config = Arr::merge($config, Koseven::load($file));
			}
		}

		return $config;
	}

}
