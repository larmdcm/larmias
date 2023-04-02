<?php

declare(strict_types=1);

namespace Larmias\Wind\Object;

class Boolean implements ObjectInterface
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
}