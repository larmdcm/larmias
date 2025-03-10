<?php

declare(strict_types=1);

namespace Larmias\ExceptionHandler\Annotation;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class ExceptionHandler
{
    public function __construct(public ?string $name = null)
    {
    }
}