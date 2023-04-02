<?php

declare(strict_types=1);

namespace Larmias\Wind\Object;

class StringObject implements ObjectInterface, Hashable
{
    public function __construct(public string $value)
    {

    }

    public function getType(): ObjectType
    {
        return new ObjectType(ObjectType::STRING_OBJ);
    }

    public function inspect(): string
    {
        return $this->value;
    }

    public function hashKey(): HashKey
    {
        return new HashKey($this->getType(), crc32($this->value) >> 16 & 0x7FFFFFFF);
    }
}