<?php
/**
 * The Encrypt Mcrypt engine provides two-way encryption of text and binary strings
 * using the [Mcrypt](http://php.net/mcrypt) extension, which consists of three
 * parts: the key, the cipher, and the mode.
 *
 * The Key
 * :  A secret passphrase that is used for encoding and decoding
 *
 * The Cipher
 * :  A [cipher](http://php.net/mcrypt.ciphers) determines how the encryption
 *    is mathematically calculated. By default, the "rijndael-128" cipher
 *    is used. This is commonly known as "AES-128" and is an industry standard.
 *
 * The Mode
 * :  The [mode](http://php.net/mcrypt.constants) determines how the encrypted
 *    data is written in binary form. By default, the "nofb" mode is used,
 *    which produces short output with high entropy.
 *
 * @package    Kohana
 * @category   Security
 * @author     Kohana Team
 * @copyright  (c) Kohana Team
 * @license    https://koseven.ga/LICENSE.md
 */
class Kohana_Encrypt_Engine_Mcrypt extends Kohana_Encrypt_Engine {

	/**
	 * @var  string  RAND type to use
	 *
	 * Only MCRYPT_DEV_URANDOM and MCRYPT_DEV_RANDOM are considered safe.
	 * Using MCRYPT_RAND will silently revert to MCRYPT_DEV_URANDOM
	 */
	protected static $_rand = MCRYPT_DEV_URANDOM;

	/**
	 * @var int the size of the Initialization Vector (IV) in bytes
	 */
	protected $_iv_size;

	/**
	 * Creates a new mcrypt wrapper.
	 *
	 * @param   mixed   $key_config    mcrypt key or config array
	 * @param   string  $mode          mcrypt mode
	 * @param   string  $cipher        mcrypt cipher
	 */
	public function __construct($key_config, $mode = NULL, $cipher = NULL)
	{
		if ($mode === NULL)
		{
			// Add the default mode
			$mode = MCRYPT_MODE_NOFB;
		}

		if ($cipher === NULL)
		{
			// Add the default cipher
			$cipher = MCRYPT_RIJNDAEL_128;
		}

		parent::__construct($key_config, $mode, $cipher);

		// Find the max length of the key, based on cipher and mode
		$size = mcrypt_get_key_size($this->_cipher, $this->_mode);

		if (isset($this->_key[$size]))
		{
			// Shorten the key to the maximum size
			$this->_key = substr($this->_key, 0, $size);
		}
		else if (version_compare(PHP_VERSION, '5.6.0', '>='))
		{
			$this->_key = $this->_normalize_key($this->_key, $this->_cipher, $this->_mode);
		}

		/*
		 * Silently use MCRYPT_DEV_URANDOM when the chosen random number generator
		 * is not one of those that are considered secure.
		 */
		if ((Encrypt_Engine_Mcrypt::$_rand !== MCRYPT_DEV_URANDOM) AND (Encrypt_Engine_Mcrypt::$_rand !== MCRYPT_DEV_RANDOM))
		{
			Encrypt_Engine_Mcrypt::$_rand = MCRYPT_DEV_URANDOM;
		}

		// Store the IV size
		$this->_iv_size = mcrypt_get_iv_size($this->_cipher, $this->_mode);
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
	public function encrypt($data, $iv)
	{
		// Encrypt the data using the configured options and generated iv
		$data = mcrypt_encrypt($this->_cipher, $this->_key, $data, $this->_mode, $iv);

		// Use base64 encoding to convert to a string
		return base64_encode($iv.$data);
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
	public function decrypt($data)
	{
		// Convert the data back to binary
		$data = base64_decode($data, TRUE);

		if ( ! $data)
		{
			// Invalid base64 data
			return FALSE;
		}

		// Extract the initialization vector from the data
		$iv = substr($data, 0, $this->_iv_size);

		if ($this->_iv_size !== strlen($iv))
		{
			// The iv is not the expected size
			return FALSE;
		}

		// Remove the iv from the data
		$data = substr($data, $this->_iv_size);

		// Return the decrypted data, trimming the \0 padding bytes from the end of the data
		return rtrim(mcrypt_decrypt($this->_cipher, $this->_key, $data, $this->_mode, $iv), "\0");
	}

	/**
	 * Proxy for the mcrypt_create_iv function - to allow mocking and testing against KAT vectors
	 *
	 * @return string the initialization vector or FALSE on error
	 */
	public function create_iv()
	{
		// Create a random initialization vector of the proper size for the current cipher
		return mcrypt_create_iv($this->_iv_size, Encrypt_Engine_Mcrypt::$_rand);
	}

	/**
	 * Normalize key for PHP 5.6 for backwards compatibility
	 *
	 * This method is a shim to make PHP 5.6 behave in a B/C way for
	 * legacy key padding when shorter-than-supported keys are used
	 *
	 * @param   string  $key    encryption key
	 * @param   string  $cipher mcrypt cipher
	 * @param   string  $mode   mcrypt mode
	 */
	protected function _normalize_key($key, $cipher, $mode)
	{
		// open the cipher
		$td = mcrypt_module_open($cipher, '', $mode, '');

		// loop through the supported key sizes
		foreach (mcrypt_enc_get_supported_key_sizes($td) as $supported) {
			// if key is short, needs padding
			if (strlen($key) <= $supported)
			{
				return str_pad($key, $supported, "\0");
			}
		}

		// at this point key must be greater than max supported size, shorten it
		return substr($key, 0, mcrypt_get_key_size($cipher, $mode));
	}

}
