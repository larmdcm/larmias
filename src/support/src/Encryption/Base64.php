<?php

declare(strict_types=1);

namespace Larmias\Support\Encryption;

use Larmias\Contracts\DataCodingInterface;
use function base64_decode;
use function base64_encode;
use function str_replace;
use function strlen;
use function substr;

class Base64 implements DataCodingInterface
{
    /**
     * @param string $data
     * @return string
     */
    public function encode(string $data): string
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }

    /**
     * @param string $data
     * @return string
     */
    public function decode(string $data): string
    {
        $data = str_replace(['-', '_'], ['+', '/'], $data);
        $mod4 = strlen($data) % 4;
        if ($mod4) {
            $data .= substr('====', $mod4);
        }
        return (string)base64_decode($data);
    }
}