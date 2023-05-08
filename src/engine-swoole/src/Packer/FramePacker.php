<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole\Packer;

use Larmias\Engine\Swoole\Contracts\PackerInterface;
use RuntimeException;
use function pack;
use function substr;
use function strlen;
use function unpack;

class FramePacker implements PackerInterface
{
    /**
     * @param string $data
     * @return string
     */
    public function pack(string $data): string
    {
        return pack('N', strlen($data) + 4) . $data;
    }

    public function unpack(string $data): array
    {
        $header = unpack('Nlength', substr($data, 0, 4));
        if ($header === false) {
            throw new RuntimeException('Invalid Header');
        }
        return [substr($data, 0, $header['length']), substr($data, $header['length'])];
    }
}