<?php

declare(strict_types=1);

namespace Larmias\Utils\Packer;

use Larmias\Contracts\PackerInterface;

class PhpSerializerPacker implements PackerInterface
{
    public function pack(mixed $data): string
    {
        return \serialize($data);
    }

    public function unpack(string $data): mixed
    {
        return \unserialize($data);
    }
}