<?php

declare(strict_types=1);

namespace Larmias\Wind\Object;

class ReturnValue implements ObjectInterface
{
    public function __construct(public ObjectInterface $value)
    {
    }

    public function getType(): ObjectType
    {
        return new ObjectType(ObjectType::RETURN_VALUE_OBJ);
    }

    public function inspect(): string
    {
        return $this->value->inspect();
    }
}