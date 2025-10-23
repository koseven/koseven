<?php

/**
 * @package    Kohana/Encrypt
 * @author     Kohana Team
 * @copyright  (c) 2007-2012 Kohana Team
 * @copyright  (c) 2016-2018 Koseven Team
 * @license    https://koseven.ga/LICENSE.md
 */
class Kohana_Encrypt {

	/**
	 * @var  string  default instance name
	 */
	public static $default = 'default';

	/**
	 * @var  array  Encrypt class instances
	 */
	public static $instances = [];

	/**
	 * @var  engine  Encryption engine
	 */
	public $_engine = NULL;

	/**
	 * Returns a singleton instance of Encrypt. An encryption key must be
	 * provided in your "encrypt" configuration file.
	 *
	 *     $encrypt = Encrypt::instance();
	 *
	 * @param   string  $name   configuration group name
	 * @return  Encrypt
	 */
	public static function instance($name = NULL, ?array $config = NULL)
	{
		if ($name === NULL)
		{
			// Use the default instance name
			$name = Encrypt::$default;
		}

		if ( ! isset(Encrypt::$instances[$name]))
		{
			if ($config === NULL)
			{
				// Load the configuration data
				$config = Kohana::$config->load('encrypt')->$name;
			}

			if ( ! isset($config['key']))
			{
				// No default encryption key is provided!
				throw new Kohana_Exception('No encryption key is defined in the encryption configuration group: :group',
					[':group' => $name]);
			}

			// Create a new instance
			Encrypt::$instances[$name] = new Encrypt($config);
		}

		return Encrypt::$instances[$name];
	}

	/**
	 * Creates a new mcrypt wrapper.
	 *
	 * @param   string  $key_config    encryption key or config array
	 * @param   string  $mode          encryption mode
	 * @param   string  $cipher        encryption cipher
	 */
	public function __construct($key_config, $mode = NULL, $cipher = NULL)
	{
		if (is_string($key_config))
		{
			$this->_engine = new Encrypt_Engine_Mcrypt($key_config, $mode, $cipher);
		}
		
		else
		{
			if ( ! isset($key_config['type']))
			{
				$key_config['type'] = 'mcrypt';
			}

			// Set the engine class name
			$engine_name = 'Encrypt_Engine_'.ucfirst($key_config['type']);

			// Create the engine class
			$this->_engine = new $engine_name($key_config);
		}
	}

	/**
	 * Encrypts a string and returns an encrypted string that can be decoded.
	 *
	 *     $data = $encrypt->encode($data);
	 *
	 * The encrypted binary data is encoded using [base64](http://php.net/base64_encode)
	 * to convert it to a string. This string can be stored in a database,
	 * displayed, and passed using most other means without corruption.
	 *
	 * @param   string  $data   data to be encrypted
	 * @return  string
	 */
	public function encode($data)
	{
		// Get an initialization vector
		$iv = $this->_create_iv();

		return $this->_engine->encrypt($data, $iv);
	}

	/**
	 * Decrypts an encoded string back to its original value.
	 *
	 *     $data = $encrypt->decode($data);
	 *
	 * @param   string  $data   encoded string to be decrypted
	 * @return  FALSE   if decryption fails
	 * @return  string
	 */
	public function decode($data)
	{
		return $this->_engine->decrypt($data);
	}

	/**
	 * Proxy for the mcrypt_create_iv function - to allow mocking and testing against KAT vectors
	 *
	 * @return string the initialization vector or FALSE on error
	 */
	protected function _create_iv()
	{
		return $this->_engine->create_iv();
	}

}
