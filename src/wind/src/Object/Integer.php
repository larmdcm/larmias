<?php

declare(strict_types=1);

namespace Larmias\Wind\Object;

class Integer implements ObjectInterface
{
    public function __construct(public int $value)
    {
    }

    public function getType(): ObjectType
    {
        return new ObjectType(ObjectType::INTEGER_OBJ);
    }

    public function inspect(): string
    {
        return strval($this->value);
    }
}