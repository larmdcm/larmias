<?php

declare(strict_types=1);

namespace Larmias\Codec\Packer;

use Larmias\Contracts\PackerInterface;

class EmptyPacker implements PackerInterface
{
    /**
     * @param mixed $data
     * @return string
     */
    public function pack(mixed $data): string
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