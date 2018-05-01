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
 * !!!WARNING This encryption engine is deprecated and should not be used. There are two reasons for deprecation:
 * 1) It uses mcrypt library which has been abandoned and possibly contains security vulnerabilities
 * 2) It doesn't authenticate ciphertexts in any way, meaning it doesn't verify if ciphertext hasn't been changed.
 * This flaw would allow malicious attacker to change the original message without the knowledge of the encryption key
 * using so called "bit flipping" attack which can't be detected.
 *
 * @package    Kohana
 * @category   Security
 * @author     Kohana Team
 * @copyright  (c) Kohana Team
 * @license    https://koseven.ga/LICENSE.md
 * @deprecated since version 3.3.8
 */
class Kohana_Encrypt_Engine_Legacy extends Kohana_Encrypt_Engine
{

    const DEFAULT_CIPHER = MCRYPT_RIJNDAEL_128;
    const ALLOWED_CIPHERS = [MCRYPT_RIJNDAEL_128, MCRYPT_RIJNDAEL_256];

    protected $_mode;

    /**
     * @var  string  RAND type to use
     *
     * Only MCRYPT_DEV_URANDOM and MCRYPT_DEV_RANDOM are considered safe.
     * Using MCRYPT_RAND will silently revert to MCRYPT_DEV_URANDOM
     */
    protected static $_rand = MCRYPT_DEV_URANDOM;

    /**
     * Kohana_Encrypt_Engine_Legacy constructor.
     * @param array $config Array with configuration
     * @throws Kohana_Exception
     */
    public function __construct(array $config)
    {
        if (!function_exists('mcrypt_encrypt')) {
            throw new Kohana_Exception('Mcrypt is not installed. Mcrypt has been deprecated as of PHP 7.2 and should not be used. Use openssl/libsodium instead.');
        }

        $this->_mode = $config['mode'] ?? MCRYPT_MODE_NOFB;

        $this->_cipher = $this->validate_cipher($config['cipher'] ?? '', self::DEFAULT_CIPHER, self::ALLOWED_CIPHERS);

        // Store the IV size
        $this->_iv_size = mcrypt_get_iv_size($this->_cipher, $this->_mode);

        // Find the max length of the key, based on cipher and mode
        $key_size = mcrypt_get_key_size($this->_cipher, $this->_mode);

        $this->_key = substr($this->validate_key_length($config['key']), 0, $key_size);
    }

    /**
     * @inheritdoc
     */
    protected function hash(string $iv, string $value)
    {

    }

    /**
     * @inheritdoc
     */
    public function create_iv(): string
    {
        return mcrypt_create_iv($this->_iv_size, self::$_rand);
    }

    /**
     * @inheritdoc
     */
    public function decrypt(string $ciphertext)
    {
        // Convert the data back to binary
        $data = base64_decode($ciphertext, TRUE);

        if (!$data) {
            // Invalid base64 data
            return FALSE;
        }

        // Extract the initialization vector from the data
        $iv = substr($data, 0, $this->_iv_size);

        if ($this->_iv_size !== strlen($iv)) {
            // The iv is not the expected size
            return FALSE;
        }

        // Remove the iv from the data
        $data = substr($data, $this->_iv_size);

        // Return the decrypted data, trimming the \0 padding bytes from the end of the data
        return rtrim(mcrypt_decrypt($this->_cipher, $this->_key, $data, $this->_mode, $iv), "\0");
    }

    /**
     * @inheritdoc
     */
    public function encrypt(string $message)
    {
        $iv = $this->create_iv();
        // Encrypt the data using the configured options and generated iv
        $data = mcrypt_encrypt($this->_cipher, $this->_key, $message, $this->_mode, $iv);

        // Use base64 encoding to convert to a string
        return base64_encode($iv . $data);
    }

}
