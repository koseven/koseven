<?php

/**
 * Class EncryptCommon
 * Helper class for Encryption engine
 */
trait EncryptCommon
{
    /**
     * Validates the payload
     * @param array $payload Payload
     * @return bool
     */
    protected function valid_payload(array $payload)
    {
        return is_array($payload) && isset($payload['iv'], $payload['value'], $payload['mac']);
    }
}