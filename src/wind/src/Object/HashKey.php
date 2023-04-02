<?php

declare(strict_types=1);

namespace Larmias\Wind\Object;

class HashKey
{
    /**
     * @param ObjectType $type
     * @param int $value
     */
    public function __construct(public ObjectType $type, public int $value)
    {
    }
}