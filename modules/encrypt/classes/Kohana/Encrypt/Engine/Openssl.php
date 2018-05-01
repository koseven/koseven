<?php

/**
 * The Encrypt Openssl engine provides two-way encryption of text and binary strings
 * using the [OpenSSL](http://php.net/openssl) extension, which consists of three
 * parts: the key, the cipher and the cryptographic hash.
 *
 * @package    Kohana
 * @category   Security
 * @author     Kohana Team
 * @copyright  (c) Kohana Team
 * @license    https://koseven.ga/LICENSE.md
 */
class Kohana_Encrypt_Engine_Openssl extends Kohana_Encrypt_Engine
{
    use EncryptCommon;

    const DEFAULT_CIPHER = 'AES-256-CTR';
    const ALLOWED_CIPHERS = ['AES-128-CBC', 'AES-128-CTR', 'AES-256-CBC', 'AES-256-CTR'];

    /**
     * Kohana_Encrypt_Engine_Openssl constructor.
     * @param array $config Array with configuration
     * @throws Kohana_Exception
     */
    public function __construct(array $config)
    {
        if (!function_exists('openssl_encrypt'))
        {
            throw new Kohana_Exception('Openssl is not installed.');
        }

        $this->_cipher = $this->validate_cipher($config['cipher'] ?? '', self::DEFAULT_CIPHER, self::ALLOWED_CIPHERS);
        $this->_hash = $this->validate_hash($config['hash'] ?? '');
        $this->_iv_size = \openssl_cipher_iv_length($this->_cipher);
        $this->_key = $this->validate_key_length($config['key'] ?? '');
    }

    protected function hash(string $iv, string $value)
    {
        return hash_hmac($this->_hash, $iv . $value, $this->_key, false);
    }

    /**
     * Validates HMAC signature
     * @param array $payload Array with payload
     * @return bool
     */
    protected function valid_mac(array $payload)
    {
        $iv = $this->create_iv();
        $calculated = hash_hmac($this->_hash, $this->hash($payload['iv'], $payload['value']), $iv, TRUE);

        return hash_equals(hash_hmac($this->_hash, $payload['mac'], $iv, TRUE), $calculated);
    }

    /**
     * @inheritdoc
     */
    public function create_iv(): string
    {
        if (function_exists('random_bytes'))
        {
            return random_bytes($this->_iv_size);
        }
        elseif (function_exists('openssl_random_pseudo_bytes'))
        {
            return openssl_random_pseudo_bytes($this->_iv_size);
        }
        else
        {
            throw new Kohana_Exception('Could not create initialization vector.');
        }
    }

    /**
     * @inheritdoc
     */
    public function decrypt(string $ciphertext)
    {
        // Convert the data back to binary
        $data = json_decode(base64_decode($ciphertext), TRUE);

        // If the payload is not valid JSON or does not have the proper keys set we will
        // assume it is invalid and bail out of the routine since we will not be able
        // to decrypt the given value.
        if (!$this->valid_payload($data))
        {
            // Decryption failed
            return FALSE;
        }

        // Let's check if MAC is valid (verify that ciphertext has not been altered in any way)
        if (!$this->valid_mac($data))
        {
            // Decryption failed
            return FALSE;
        }

        $iv = base64_decode($data['iv']);
        if (!$iv)
        {
            // Invalid base64 data
            return FALSE;
        }

        // Here we will decrypt the value. If we are able to successfully decrypt it
        // we will then return it out to the caller. If we are
        // unable to decrypt this value we will return FALSE
        $decrypted = \openssl_decrypt($data['value'], $this->_cipher, $this->_key, 0, $iv);

        if ($decrypted === FALSE)
        {
            return FALSE;
        }

        return $decrypted;
    }

    /**
     * @inheritdoc
     */
    public function encrypt(string $message)
    {
        // First we will encrypt the value using OpenSSL. After this is encrypted we
        // will proceed to calculating a MAC for the encrypted value so that this
        // value can be verified later as not having been changed by the users.
        $iv = $this->create_iv();
        $value = \openssl_encrypt($message, $this->_cipher, $this->_key, 0, $iv);

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

        if (!is_string($json))
        {
            // Encryption failed
            return FALSE;
        }

        return base64_encode($json);
    }
}
