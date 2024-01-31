<?php

declare(strict_types=1);

namespace Larmias\Codec\Packer;

use Larmias\Contracts\PackerInterface;
use function serialize;
use function unserialize;

class PhpSerializerPacker implements PackerInterface
{
    /**
     * @param mixed $data
     * @return string
     */
    public function pack(mixed $data): string
    {
        return serialize($data);
    }

    /**
     * @param string $data
     * @return mixed
     */
    public function unpack(string $data): mixed
    {
        return unserialize($data);
    }
}