<?php

declare(strict_types=1);

namespace Larmias\Wind\Object;

class HashParis
{
    public function __construct(public ObjectInterface $key, public ObjectInterface $value)
    {
    }
}