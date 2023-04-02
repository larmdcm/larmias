<?php

declare(strict_types=1);

namespace Larmias\Wind\Object;

class Boolean implements ObjectInterface, Hashable
{
    public function __construct(public bool $value)
    {
    }

    public function getType(): ObjectType
    {
        return new ObjectType(ObjectType::BOOLEAN_OBJ);
    }

    public function inspect(): string
    {
        return $this->value ? 'true' : 'false';
    }

    public function hashKey(): HashKey
    {
        return new HashKey($this->getType(), $this->value ? 1 : 0);
    }
}