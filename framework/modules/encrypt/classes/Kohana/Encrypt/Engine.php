<?php

abstract class Kohana_Encrypt_Engine {

	/**
	 * @var string Encryption key
	 */
	protected $_key;

	/**
	 * @var string mcrypt mode
	 */
	protected $_mode;

	/**
	 * @var string mcrypt cipher
	 */
	protected $_cipher;

	/**
	 * Creates a new mcrypt wrapper.
	 *
	 * @param   mixed   $key_config    mcrypt key or config array
	 * @param   string  $mode          mcrypt mode
	 * @param   string  $cipher        mcrypt cipher
	 */
	public function __construct($key_config, $mode = NULL, $cipher = NULL)
	{
		if (is_array($key_config))
		{
			if (isset($key_config['key']))
			{
				$this->_key = $key_config['key'];
			}
			else
			{
				// No default encryption key is provided!
				throw new Kohana_Exception('No encryption key is defined in the encryption configuration');
			}

			if (isset($key_config['mode']))
			{
				$this->_mode = $key_config['mode'];
			}
			// Mode not specified in config array, use argument
			else if ($mode !== NULL)
			{
				$this->_mode = $mode;
			}
			
			if (isset($key_config['cipher']))
			{
				$this->_cipher = $key_config['cipher'];
			}
			// Cipher not specified in config array, use argument
			else if ($cipher !== NULL)
			{
				$this->_cipher = $cipher;
			}
		}
		else if (is_string($key_config))
		{
			// Store the key, mode, and cipher
			$this->_key = $key_config;
			$this->_mode = $mode;
			$this->_cipher = $cipher;
		}
		else
		{
			// No default encryption key is provided!
			throw new Kohana_Exception('No encryption key is defined in the encryption configuration');
		}
	}

	abstract public function encrypt($data, $iv);
	abstract public function decrypt($data);

}
