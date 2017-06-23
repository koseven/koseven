<?php
/**
 * The Encrypt Openssl engine provides two-way encryption of text and binary strings
 * using the [OpenSSL](http://php.net/openssl) extension, which consists of two
 * parts: the key and the cipher.
 *
 * The Key
 * :  A secret passphrase that is used for encoding and decoding
 *
 * The Cipher
 * :  A [cipher](http://php.net/manual/en/openssl.ciphers.php) determines how the encryption
 *    is mathematically calculated. By default, the "AES-256-CBC" cipher
 *    is used.
 *
 *
 * @package    Kohana
 * @category   Security
 * @author     Kohana Team
 * @copyright  (c) Kohana Team
 * @license    https://koseven.ga/LICENSE.md
 */
class Kohana_Encrypt_Engine_Openssl extends Kohana_Encrypt_Engine {

	/**
	 * @var int the size of the Initialization Vector (IV) in bytes
	 */
	protected $_iv_size;

	/**
	 * Creates a new openssl wrapper.
	 *
	 * @param   string  $key    encryption key
	 * @param   string  $mode   openssl mode
	 * @param   string  $cipher openssl cipher
	 */
	public function __construct($key_config, $mode = NULL, $cipher = NULL)
	{
		if ($cipher === NULL)
		{
			// Add the default cipher
			$cipher = 'AES-256-CBC';
		}

		parent::__construct($key_config, $mode, $cipher);

		$this->_iv_size = openssl_cipher_iv_length($this->_cipher);

		$length = mb_strlen($this->_key, '8bit');

		// Validate configuration
		if ($this->_cipher === 'AES-128-CBC')
		{
			if ($length !== 16)
			{
				// No valid encryption key is provided!
				throw new Kohana_Exception('No valid encryption key is defined in the encryption configuration: length should be 16 for AES-128-CBC');
			}
		}
			
		elseif ($this->_cipher === 'AES-256-CBC')
		{
			if ($length !== 32)
			{
				// No valid encryption key is provided!
				throw new Kohana_Exception('No valid encryption key is defined in the encryption configuration: length should be 32 for AES-256-CBC');
			}
		}
		
		else
		{
			// No valid encryption cipher is provided!
			throw new Kohana_Exception('No valid encryption cipher is defined in the encryption configuration.');
		}
	}

	/**
	 * Encrypts a string and returns an encrypted string that can be decoded.
	 *
	 * @param   string  $data   data to be encrypted
	 * @return  string
	 */
	public function encrypt($data, $iv)
	{
		// First we will encrypt the value using OpenSSL. After this is encrypted we
		// will proceed to calculating a MAC for the encrypted value so that this
		// value can be verified later as not having been changed by the users.
		$value = \openssl_encrypt($data, $this->_cipher, $this->_key, 0, $iv);

		if ($value === FALSE)
		{
			// Encryption failed
			return FALSE;
		}

		// Once we have the encrypted value we will go ahead base64_encode the input
		// vector and create the MAC for the encrypted value so we can verify its
		// authenticity. Then, we'll JSON encode the data in a "payload" array.
		$mac = $this->hash($iv = base64_encode($iv), $value);

		$json = json_encode(compact('iv', 'value', 'mac'));

		if (! is_string($json))
		{
			// Encryption failed
			return FALSE;
		}

		return base64_encode($json);
	}

	/**
	 * Decrypts an encoded string back to its original value.
	 *
	 * @param   string  $data   encoded string to be decrypted
	 * @return  FALSE   if decryption fails
	 * @return  string
	 */
	public function decrypt($data)
	{
		// Convert the data back to binary
		$data = json_decode(base64_decode($data), TRUE);

		// If the payload is not valid JSON or does not have the proper keys set we will
		// assume it is invalid and bail out of the routine since we will not be able
		// to decrypt the given value. We'll also check the MAC for this encryption.
		if ( ! $this->valid_payload($data))
		{
			// Decryption failed
			return FALSE;
		}

		if ( ! $this->valid_mac($data))
		{
			// Decryption failed
			return FALSE;
		}

		$iv = base64_decode($data['iv']);
		if ( ! $iv)
		{
			// Invalid base64 data
			return FALSE;
		}

		// Here we will decrypt the value. If we are able to successfully decrypt it
		// we will then unserialize it and return it out to the caller. If we are
		// unable to decrypt this value we will throw out an exception message.
		$decrypted = \openssl_decrypt($data['value'], $this->_cipher, $this->_key, 0, $iv);

		if ($decrypted === FALSE)
		{
			return FALSE;
		}

		return $decrypted;
	}

	/**
	 * Create a MAC for the given value.
	 *
	 * @param  string  $iv
	 * @param  mixed  $value
	 * @return string
	 */
	protected function hash($iv, $value)
	{
		return hash_hmac('sha256', $iv.$value, $this->_key);
	}

	/**
	 * Verify that the encryption payload is valid.
	 *
	 * @param  mixed  $payload
	 * @return bool
	 */
	protected function valid_payload($payload)
	{
		return is_array($payload) AND
					 isset($payload['iv'], $payload['value'], $payload['mac']);
	}

	/**
	 * Determine if the MAC for the given payload is valid.
	 *
	 * @param  array  $payload
	 * @return bool
	 */
	protected function valid_mac(array $payload)
	{
		$bytes = $this->create_iv($this->_iv_size);
		$calculated = hash_hmac('sha256', $this->hash($payload['iv'], $payload['value']), $bytes, TRUE);

		return hash_equals(hash_hmac('sha256', $payload['mac'], $bytes, TRUE), $calculated);
	}
		
	/**
	 * Proxy for the random_bytes function - to allow mocking and testing against KAT vectors
	 *
	 * @return string the initialization vector or FALSE on error
	 */
	public function create_iv()
	{
		if (function_exists('random_bytes'))
		{
			return random_bytes($this->_iv_size);
		}

		if ((PHP_VERSION_ID >= 50307) AND (function_exists('mcrypt_create_iv')))
		{
			$key = mcrypt_create_iv($this->_iv_size, MCRYPT_DEV_URANDOM);
			if (mb_strlen($key, '8bit') === $this->_iv_size)
			{
				return $key;
			}
		}

		throw new Kohana_Exception('Could not create initialization vector.');
	}

}
