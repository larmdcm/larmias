<?php

declare(strict_types=1);

namespace Larmias\Utils\Packer;

use Larmias\Contracts\PackerInterface;

class FrameSerializer implements PackerInterface
{
    public const SERIALIZE_PREFIX = 'larmias_serialize';

    public function pack(mixed $data): string
    {
        return \strval(\is_scalar($data) ? $data : self::SERIALIZE_PREFIX . \serialize($data));
    }

    public function unpack(string $data): mixed
    {
        if (\str_starts_with($data, self::SERIALIZE_PREFIX)) {
            return \unserialize(\substr($data, \strlen(self::SERIALIZE_PREFIX)));
        }
        return false;
    }
}