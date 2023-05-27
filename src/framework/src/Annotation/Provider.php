<?php

declare(strict_types=1);

namespace Larmias\Framework\Annotation;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Provider
{
    public function __construct()
    {
    }
}