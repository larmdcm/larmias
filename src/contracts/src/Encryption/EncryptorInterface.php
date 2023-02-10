<?php

declare(strict_types=1);

namespace Larmias\Contracts\Encryption;

interface EncryptorInterface
{
    /**
     * @param string $data
     * @param array $params
     * @return string
     */
    public function encrypt(string $data, array $params = []): string;

    /**
     * @param string $data
     * @param array $params
     * @return string
     */
    public function decrypt(string $data, array $params = []): string;

    /**
     * @param int $length
     * @return string
     */
    public function generateKey(int $length = 32): string;

    /**
     * @return string
     */
    public function getKey(): string;
}