<?php

namespace Larmias\Wind\Object;

class Nil implements ObjectInterface
{

    public function getType(): ObjectType
    {
        return new ObjectType(ObjectType::NIL_OBJ);
    }

    public function inspect(): string
    {
        return 'nil';
    }
}