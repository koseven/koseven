<?php

/**
 * Class Kohana_Encrypt_Engine_Libsodium
 * @package    Kohana
 * @category   Security
 * @author     Kohana Team
 * @copyright  (c) Kohana Team
 * @license    https://koseven.ga/LICENSE.md
 */
class Kohana_Encrypt_Engine_Libsodium extends Kohana_Encrypt_Engine
{
    use EncryptCommon;

    /**
     * Kohana_Encrypt_Engine_Libsodium constructor.
     * @param array $config Array with configuration
     */
    public function __construct(array $config)
    {
        $this->_hash = $this->validate_hash($config['hash'] ?? '');
        $this->_key = $this->validate_key_length($config['key'] ?? '');
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
        return random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
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

        // If payload validated -> try to decode all the elements
        foreach ($data as $key => $element)
        {
            $decode = base64_decode($element);
            if (!$decode)
            {
                return FALSE;
            }
            $data[$key] = $decode;
        }

        // Let's check if MAC is valid (verify that ciphertext has not been altered in any way)
        if (!$this->valid_mac($data))
        {
            // Decryption failed
            return FALSE;
        }

        // Here we will decrypt the value. If we are able to successfully decrypt it
        // we will then return it out to the caller. If we are
        // unable to decrypt this value we will return FALSE
        $plaintext = sodium_crypto_secretbox_open($data['value'], $data['iv'], $this->_key);
        if ($plaintext === FALSE)
        {
            return FALSE;
        }

        return $plaintext;
    }

    /**
     * Validates the authentication of the ciphertext
     * @param array $payload Payload
     * @return mixed
     */
    protected function valid_mac(array $payload)
    {
        return sodium_crypto_auth_verify($payload['mac'], $payload['value'], $this->_key);
    }

    /**
     * @inheritdoc
     */
    public function encrypt(string $message)
    {
        // Each time create new IV
        $iv = $this->create_iv();

        // Here we encode the message
        $value = sodium_crypto_secretbox($message, $iv, $this->_key);

        // We authenticate the message
        $mac = sodium_crypto_auth($value, $this->_key);

        $result = compact('iv', 'value', 'mac');
        foreach ($result as $key => $element)
        {
            $result[$key] = base64_encode($element);
        }

        $json = json_encode($result);

        if (!is_string($json))
        {
            // Encryption failed
            return FALSE;
        }

        return base64_encode($json);
    }

}
