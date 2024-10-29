<?php

declare(strict_types=1);

namespace Larmias\Codec\Packer;

use Larmias\Codec\SecureBase64;
use Larmias\Contracts\PackerInterface;

class SecureBase64Packer implements PackerInterface
{
    /**
     * @param mixed $data
     * @return string
     */
    public function pack(mixed $data): string
    {
        return SecureBase64::encode($data);
    }

    /**
     * @param string $data
     * @return string|bool
     */
    public function unpack(string $data): string|false
    {
        return SecureBase64::decode($data);
    }
}