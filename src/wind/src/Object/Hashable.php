<?php

declare(strict_types=1);

namespace Larmias\Wind\Object;

interface Hashable
{
    public function hashKey(): HashKey;
}