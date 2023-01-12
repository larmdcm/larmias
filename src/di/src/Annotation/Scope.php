<?php

declare(strict_types=1);

namespace Larmias\Di\Annotation;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
class Scope extends AnnotationAbstract
{
    /**
     * @var string
     */
    public const SINGLETON = 'singleton';

    /**
     * @var string
     */
    public const PROTOTYPE = 'prototype';

    /**
     * Scope constructor.
     *
     * @param string $type
     */
    public function __construct(public string $type)
    {
    }
}