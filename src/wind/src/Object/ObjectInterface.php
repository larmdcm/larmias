<?php

declare(strict_types=1);

namespace Larmias\Wind\Object;

interface ObjectInterface
{
    public function getType(): ObjectType;

    public function inspect(): string;
}