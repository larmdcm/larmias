<?php

declare(strict_types=1);

namespace Larmias\Wind\Object;

class Builtin implements ObjectInterface
{
    public function __construct(public BuiltinFunction $fn)
    {
    }

    public function getType(): ObjectType
    {
        return new ObjectType(ObjectType::BUILTIN_OBJ);
    }

    public function inspect(): string
    {
        return 'builtin function';
    }
}