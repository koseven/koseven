<?php
/**
 * Abstract Encryption Engine Base Class.
 * Holds Encryption Key, Mode, Cipher and IV Size.
 *
 * Integrated Key Length Validation. Removes Key from var_dump
 * to avoid dumping encryption key.
 *
 * @category   Security
 * @package    KO7/Encrypt
 *
 * @copyright  (c) 2007-2012 Kohana Team
 * @copyright  (c) 2016-2018 Koseven Team
 * @license    https://koseven.dev/LICENSE
 */
abstract class KO7_Encrypt_Engine {

	/**
	 * Encryption key
	 * @var string
	 */
	protected $_key;

	/**
	 * Encryption Mode
	 * @var string
	 */
	protected $_mode;

	/**
	 * Cipher
	 * @var string
	 */
	protected $_cipher;

	/**
	 * The size of the Initialization Vector (IV) in bytes
	 * @var int
	 */
	protected $_iv_size;

	/**
	 * Creates a new Encrypt object.
	 *
	 * @param  array $config Configuration
	 *
	 * @throws KO7_Exception
	 */
	public function __construct(array $config)
	{
		$this->_key = $config['key'];
	}

	/**
	 * Encrypts the message
	 *
	 * @param string $message Your message to be encrypted.
	 * @param string $iv
	 *
	 * @return null|string
	 */
	abstract public function encrypt(string $message, string $iv);

	/**
	 * Decrypts the ciphertext
	 *
	 * @param string $ciphertext Your ciphertext to be decrypted.
	 *
	 * @return null|string
	 */
	abstract public function decrypt(string $ciphertext);

	/**
	 * Creates random IV (Initialization vector) for each encryption action.
	 *
	 * @throws Exception     Not possible to gather sufficient entropy.
	 * @return string         Initialization Vector
	 */
	public function create_iv(): string
	{
		if (function_exists('random_bytes')) {
			return random_bytes($this->_iv_size);
		}
		// @codeCoverageIgnoreStart
		throw new KO7_Exception('Could not create initialization vector.');
		// @codeCoverageIgnoreEnd
	}

	/**
	 * Override __debugInfo function to not display key in var_dump
	 *
	 * @codeCoverageIgnore
	 * @return array
	 */
	public function __debugInfo()
	{
		$result = get_object_vars($this);
		unset($result['_key']);
		return $result;
	}

	/**
	 * Check if key has valid length
	 *
	 * @param  int $expected Expected Key Length
	 *
	 * @throws KO7_Exception
	 */
	protected function valid_key_length($expected)
	{
		$length = mb_strlen($this->_key, '8bit');
		if ($length !== $expected) {
			throw new KO7_Exception('No valid encryption key is defined in the encryption configuration: length should be :required_length for :cipher, is: :current_length', [
				':cipher' => $this->_cipher,
				':required_length' => $expected,
				':current_length' => $length
			]);
		}
	}
}
