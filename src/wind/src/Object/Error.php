<?php

declare(strict_types=1);

namespace Larmias\Wind\Object;

class Error implements ObjectInterface
{
    public function __construct(public string $message)
    {
    }

    public function getType(): ObjectType
    {
        return new ObjectType(ObjectType::ERROR_OBJ);
    }

    public function inspect(): string
    {
        return 'ERROR: ' . $this->message;
    }
}