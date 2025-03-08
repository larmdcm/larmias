<?php

declare(strict_types=1);

namespace Larmias\Encryption\Driver;

use Larmias\Contracts\Encryption\EncryptException;
use Throwable;

class OpenSSL extends Driver
{
    /**
     * @var array|string[]
     */
    protected array $config = [
        'key' => null,
        'iv' => null,
        'packer' => null,
        'cipher' => 'aes-128-cbc',
        'options' => \OPENSSL_RAW_DATA,
        'digest' => 'SHA512',
    ];

    /**
     * The supported cipher algorithms and their properties.
     *
     * @var array
     */
    protected static array $supportedCiphers = [
        'aes-128-cbc' => ['size' => 16, 'aead' => false],
        'aes-256-cbc' => ['size' => 32, 'aead' => false],
        'aes-128-gcm' => ['size' => 16, 'aead' => true],
        'aes-256-gcm' => ['size' => 32, 'aead' => true],
    ];

    /**
     * @param string $data
     * @param array $params
     * @return string
     * @throws \Throwable
     */
    public function encrypt(string $data, array $params = []): string
    {
        $key = $params['key'] ?? $this->config['key'];
        if (empty($key)) {
            throw new EncryptException('The encryption key does not exist.');
        }
        $cipher = $params['cipher'] ?? $this->config['cipher'];
        $iv = ($ivSize = \openssl_cipher_iv_length($cipher)) ? ($params['iv'] ?? \openssl_random_pseudo_bytes($ivSize)) : null;
        $options = $params['options'] ?? $this->config['options'];
        $digest = $params['digest'] ?? $this->config['digest'];
        $secret = \hash_hkdf($digest, $key);
        $value = \openssl_encrypt(
            $data, \strtolower($cipher), $secret, $options, $iv
        );

        if ($value === false) {
            throw new EncryptException('Could not encrypt the data.');
        }
        // derive a secret key
        $secret = \hash_hkdf($digest, $key);
        $result = $iv . $value;

        $hmacKey = \hash_hmac($digest, $result, $secret, true);

        return $this->encode($hmacKey . $result);
    }

    /**
     * @param string $data
     * @param array $params
     * @return string
     */
    public function decrypt(string $data, array $params = []): string
    {
        $key = $params['key'] ?? $this->config['key'];
        if (empty($key)) {
            throw new EncryptException('The encryption key does not exist.');
        }
        $data = $this->decode($data);
        $digest = $params['digest'] ?? $this->config['digest'];
        $secret = \hash_hkdf($digest, $key);
        $hmacLength = self::substr($digest, 3) / 8;
        $hmacKey = self::substr($data, 0, $hmacLength);
        $data = self::substr($data, $hmacLength);
        $hmacCalc = \hash_hmac($digest, $data, $secret, true);

        if (!\hash_equals($hmacKey, $hmacCalc)) {
            throw new EncryptException('Hmac comparison failed.');
        }

        $cipher = $params['cipher'] ?? $this->config['cipher'];
        $options = $params['options'] ?? $this->config['options'];

        if ($ivSize = \openssl_cipher_iv_length($cipher)) {
            $iv = self::substr($data, 0, $ivSize);
            $data = self::substr($data, $ivSize);
        } else {
            $iv = null;
        }
        $value = \openssl_decrypt($data, $cipher, $secret, $options, $iv);
        if ($value === false) {
            throw new EncryptException('Could not decrypt the data.');
        }
        return $value;
    }

    /**
     * @param int $length
     * @return string
     * @throws Throwable
     */
    public function generateKey(int $length = 32): string
    {
        return \random_bytes(self::$supportedCiphers[\strtolower($this->config['cipher'])]['size'] ?? $length);
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->config['key'];
    }

    /**
     * Byte-safe substr()
     *
     * @param string $str
     * @param int $start
     * @param int|null $length
     *
     * @return string
     */
    protected static function substr(string $str, int $start, ?int $length = null): string
    {
        return \mb_substr($str, $start, $length, '8bit');
    }
}