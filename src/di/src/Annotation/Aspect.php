<?php

declare(strict_types=1);

namespace Larmias\Di\Annotation;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Aspect
{
    /**
     * @param array $classes
     * @param array $annotations
     */
    public function __construct(public array $classes = [], public array $annotations = [])
    {
    }
}