<?php
/**
 * Helper Class for Unit Tests.
 *
 * @package    Kohana/UnitTest
 *
 * @author     Koseven Team
 * @copyright  (c) 2007-2012 Kohana Team
 * @copyright  (c) 2016-2018 Koseven Team
 * @license    https://koseven.ga/LICENSE.md
 */
class Kohana_Unittest_Helpers {

	/**
	 * Static variable used to work out whether we have an internet connection
	 * @var boolean
	 */
	protected static $_has_internet;

	/**
	 * Backup of the environment variables
	 * @var array
	 */
	protected $_environment_backup = [];

	/**
	 * Check for internet connectivity
	 * @return boolean Whether an internet connection is available
	 */
	public static function has_internet() : bool
	{
		if ( ! isset(self::$_has_internet))
		{
			// The @ operator is used here to avoid DNS errors when there is no connection.
			$sock = @fsockopen("www.google.com", 80, $errno, $errstr, 1);

			self::$_has_internet = (bool) $sock ? TRUE : FALSE;
		}

		return self::$_has_internet;
	}

	/**
	 * Helper function which replaces the "/" to OS-specific delimiter
	 *
	 * @param  string $path
	 *
	 * @return string
	 */
	public static function dir_separator($path) : string
	{
		return str_replace('/', DIRECTORY_SEPARATOR, $path);
	}

	/**
	 * Removes all cache files from the kohana cache dir (except .gitignore)
	 * @return void
	 */
	public static function clean_cache_dir()
	{
		$files = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator(Kohana::$cache_dir, RecursiveDirectoryIterator::SKIP_DOTS),
			RecursiveIteratorIterator::CHILD_FIRST
		);

		foreach ($files as $file) {
			if ($file->getExtension() === 'gitignore') {
				continue;
			}
			$todo = ($file->isDir() ? 'rmdir' : 'unlink');
			$todo($file->getRealPath());
		}
	}

	/**
	 * Allows easy setting & backing up of enviroment config
	 *
	 * Option types are checked in the following order:
	 *
	 * - Server Var
	 * - Static Variable
	 * - Config option
	 *
	 * @param  array $environment List of environment to set
	 *
	 * @return bool
	 * @throws Kohana_Exception
	 * @throws ReflectionException
	 */
	public function set_environment(array $environment) : bool
	{
		if ( ! count($environment)) {
			return FALSE;
		}

		foreach ($environment as $option => $value)
		{
			$backup_needed = ! array_key_exists($option, $this->_environment_backup);

			// Handle changing superglobals
			if (in_array($option, ['_GET', '_POST', '_SERVER', '_FILES']))
			{
				// For some reason we need to do this in order to change the superglobals
				global $$option;

				if ($backup_needed)
				{
					$this->_environment_backup[$option] = $$option;
				}

				// PHPUnit makes a backup of superglobals automatically
				$$option = $value;
			}
			// If this is a static property i.e. Html::$windowed_urls
			elseif (strpos($option, '::$') !== FALSE)
			{
				list($class, $var) = explode('::$', $option, 2);

				$class = new ReflectionClass($class);

				if ($backup_needed)
				{
					$this->_environment_backup[$option] = $class->getStaticPropertyValue($var);
				}

				$class->setStaticPropertyValue($var, $value);
			}
			// If this is an environment variable
			elseif (preg_match('/^[A-Z_-]+$/', $option) OR isset($_SERVER[$option]))
			{
				if ($backup_needed)
				{
					$this->_environment_backup[$option] = isset($_SERVER[$option]) ? $_SERVER[$option] : '';
				}

				$_SERVER[$option] = $value;
			}
			// Else we assume this is a config option
			else
			{
				if ($backup_needed)
				{
					$this->_environment_backup[$option] = Kohana::$config->load($option);
				}

				list($group, $var) = explode('.', $option, 2);

				Kohana::$config->load($group)->set($var, $value);
			}
		}
		return TRUE;
	}

	/**
	 * Restores the environment to the original state
	 *
	 * @chainable
	 * @throws Kohana_Exception
	 * @throws ReflectionException
	 */
	public function restore_environment()
	{
		$this->set_environment($this->_environment_backup);
	}
}
