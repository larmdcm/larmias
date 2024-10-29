<?php

declare(strict_types=1);

namespace Larmias\Config\Annotation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Value
{
    public function __construct(public ?string $key = null, public mixed $default = null)
    {
    }
}