<?php

declare(strict_types=1);

namespace Larmias\Codec\Packer;

use Larmias\Contracts\PackerInterface;
use function base64_encode;
use function base64_decode;
use function str_replace;
use function strlen;
use function substr;

class Base64Packer implements PackerInterface
{
    /**
     * @param mixed $data
     * @return string
     */
    public function pack(mixed $data): string
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }

    /**
     * @param string $data
     * @return string|bool
     */
    public function unpack(string $data): string|bool
    {
        $data = str_replace(['-', '_'], ['+', '/'], $data);
        $mod4 = strlen($data) % 4;
        if ($mod4) {
            $data .= substr('====', $mod4);
        }
        return base64_decode($data);
    }
}