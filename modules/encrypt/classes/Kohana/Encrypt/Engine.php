<?php

/**
 * Class Kohana_Encrypt_Engine
 * Template for all encryption engines
 */
abstract class Kohana_Encrypt_Engine
{
    /**
     * @var array Available hashes (As of 2017 only sha-2 hashes are considered secure)
     */
    const ALLOWED_HASHES = ['sha256', 'sha384', 'sha512'];
    /**
     * @var string The default hash
     */
    const DEFAULT_HASH = 'sha256';

    /**
     * @var string Encryption key
     */
    protected $_key;
    /**
     * @var string Chosen cipher
     */
    protected $_cipher;
    /**
     * @var string Iv size for chosen cipher
     */
    protected $_iv_size;
    /**
     * @var string The chosen hash
     */
    protected $_hash;

    /**
     * Encrypts the data
     * Returns FALSE when failed, otherwise the encrypted message
     * @param string $message The message to encrypt
     * @return bool|string
     */
    abstract public function encrypt(string $message);

    /**
     * Decrypts the ciphertext
     * returns FALSE when failed, the original message otherwise
     * @param string $ciphertext
     * @return bool|string
     */
    abstract public function decrypt(string $ciphertext);

    /**
     * Create IV (initialization vector) for the encryption
     * WARNING!!! New IV should be created for each encryption data
     * @return string Initialization vector
     */
    abstract public function create_iv(): string;

    /**
     * Hashes data
     * @param string $iv Initialization vector
     * @param string $value Encrypted data to be hashed
     * @return mixed
     */
    abstract protected function hash(string $iv, string $value);

    /**
     * Validates key length
     * Returns the key if the key is valid otherwise throws Kohana_Exception
     * @param string $key Encryption/Decryption key
     * @return string Encryption/Decryption key
     * @throws Kohana_Exception
     */
    protected function validate_key_length(string $key): string
    {
        $length = mb_strlen($key, '8bit');

        //Require at least 128bit key
        if ($length < 16)
        {
            throw new Kohana_Exception('The provided key is not valid. Key must be at least 16 characters long.');
        }

        return $key;
    }

    /**
     * Validates if the cipher used is one of secure ones (only AES CBC and CTR are considered secure)
     * Returns cipher name if cipher is in list $ciphers otherwise returns $default_cipher
     * @param string $cipher Name of the cipher
     * @param string $default_cipher Default cipher if provided one is invalid
     * @param array $ciphers List of allowed ciphers
     * @return string Name of chosen cipher
     */
    protected function validate_cipher(string $cipher, string $default_cipher, array $ciphers = [])
    {
        if (in_array($cipher, $ciphers, TRUE))
        {
            return $cipher;
        }
        else
        {
            return $default_cipher;
        }
    }

    /**
     * Validates if cipher is on the list of allowed hashing functions
     * As of 2017 only sha-2 cryptographic function are considered secure
     * @param string $hash Hash name
     * @return string If hash name is on allowed hashes list then returns its name otherwise
     * returns name of the default chosen hash
     */
    protected function validate_hash(string $hash)
    {
        if (in_array($hash, self::ALLOWED_HASHES, TRUE))
        {
            return $hash;
        }
        else
        {
            return self::DEFAULT_HASH;
        }
    }

}
