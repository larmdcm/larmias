<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole\Packer;

use Larmias\Engine\Swoole\Contracts\PackerInterface;

class EmptyPacker implements PackerInterface
{
    /**
     * @param string $data
     * @return string
     */
    public function pack(string $data): string
    {
        return $data;
    }

    /**
     * @param string $data
     * @return string[]
     */
    public function unpack(string $data): array
    {
        return [$data, ''];
    }
}