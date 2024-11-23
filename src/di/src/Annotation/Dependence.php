<?php

declare(strict_types=1);

namespace Larmias\Di\Annotation;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Dependence
{
    public function __construct(public ?string $provider = null, public bool $force = true)
    {
    }
}