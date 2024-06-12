<?php
/**
 * The Encrypt Openssl engine provides two-way encryption of text and binary strings
 * using the [OpenSSL](http://php.net/openssl) extension, which consists of two
 * parts: the key and the cipher.
 *
 * The Key
 * :  A secret passphrase that is used for encoding and decoding
 * The Cipher
 * :  A [cipher](http://php.net/manual/en/openssl.ciphers.php) determines how the encryption
 *    is mathematically calculated. By default, the "AES-256-CBC" cipher is used.
 *
 * @package    KO7/Encrypt
 * @category   Security
 *
 * @copyright  (c) 2007-2012 Kohana Team
 * @copyright  (c) 2016-2018 Koseven Team
 * @license    https://koseven.dev/LICENSE
 */
class KO7_Encrypt_Engine_Openssl extends KO7_Encrypt_Engine {

	/**
	 * AES_128_CBC
	 * @var    string
	 */
	const AES_128_CBC = 'AES-128-CBC';

	/**
	 * AES_256_CBC
	 * @var    string
	 */
	const AES_256_CBC = 'AES-256-CBC';

	/**
	 * Creates a new openssl wrapper.
	 *
	 * @param  array $config Configuration
	 *
	 * @throws KO7_Exception
	 */
	public function __construct(array $config)
	{
		if (!extension_loaded('openssl')) {
			// @codeCoverageIgnoreStart
			throw new KO7_Exception('OpenSSL extension is not installed.');
			// @codeCoverageIgnoreEnd
		}

		parent::__construct($config);

		$this->_cipher = $config['cipher'] ?? self::AES_256_CBC;

		$this->_iv_size = openssl_cipher_iv_length($this->_cipher);

		$required_length = $this->_cipher === self::AES_128_CBC ? 16 : 32;

		$this->valid_key_length($required_length);
	}

	/**
	 * Encrypts a string and returns an encrypted string that can be decoded.
	 *
	 * @param string $data
	 * @param string $iv
	 *
	 * @return null|string
	 */
	public function encrypt(string $data, string $iv)
	{
		// First we will encrypt the value using OpenSSL. After this is encrypted we
		// will proceed to calculating a MAC for the encrypted value so that this
		// value can be verified later as not having been changed by the users.
		$value = openssl_encrypt($data, $this->_cipher, $this->_key, 0, $iv);

		// Once we have the encrypted value we will go ahead base64_encode the input
		// vector and create the MAC for the encrypted value so we can verify its
		// authenticity. Then, we'll JSON encode the data in a "payload" array.
		$mac = $this->hash($iv = base64_encode($iv), $value);

		$json = json_encode(compact('iv', 'value', 'mac'));

		return !is_string($json) ? NULL : base64_encode($json);
	}

	/**
	 * Create a MAC for the given value.
	 *
	 * @param  string $iv
	 * @param  mixed  $value
	 *
	 * @return string
	 */
	protected function hash($iv, $value): string
	{
		return hash_hmac('sha256', $iv.$value, $this->_key);
	}

	/**
	 * Decrypts an encoded string back to its original value.
	 *
	 * @param   string $data encoded string to be decrypted
	 *
	 * @return NULL|string if decryption fails
	 * @throws Exception
	 */
	public function decrypt(string $data)
	{
		// Convert the data back to binary
		$data = json_decode(base64_decode($data), TRUE);

		// If the payload is not valid JSON or does not have the proper keys set we will
		// assume it is invalid and bail out of the routine since we will not be able
		// to decrypt the given value. We'll also check the MAC for this encryption.
		$iv = base64_decode($data['iv']);
		if (!$iv || !$this->valid_payload($data) || !$this->valid_mac($data)) {
			// Decryption failed
			return NULL;
		}

		// Here we will decrypt the value. If we are able to successfully decrypt it
		// we will then unserialize it and return it out to the caller. If we are
		// unable to decrypt this value we will return NULL.
		$decrypted = openssl_decrypt($data['value'], $this->_cipher, $this->_key, 0, $iv);

		return $decrypted === FALSE ? NULL : $decrypted;
	}

	/**
	 * Verify that the encryption payload is valid.
	 *
	 * @param  mixed $payload
	 *
	 * @return bool
	 */
	protected function valid_payload($payload): bool
	{
		return is_array($payload) &&
			isset($payload['iv'], $payload['value'], $payload['mac']) &&
			strlen(base64_decode($payload['iv'], TRUE)) === $this->_iv_size;
	}

	/**
	 * Determine if the MAC for the given payload is valid.
	 *
	 * @param  array $payload
	 *
	 * @return bool
	 * @throws Exception
	 */
	protected function valid_mac(array $payload): bool
	{
		$bytes = $this->create_iv();
		$calculated = hash_hmac('sha256', $this->hash($payload['iv'], $payload['value']), $bytes, TRUE);
		return hash_equals(hash_hmac('sha256', $payload['mac'], $bytes, TRUE), $calculated);
	}
}
