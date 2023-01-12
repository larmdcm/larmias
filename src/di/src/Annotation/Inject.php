<?php

declare(strict_types=1);

namespace Larmias\Di\Annotation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Inject extends AnnotationAbstract
{
    public function __construct(public ?string $name = null, public bool $required = true, public ?string $scope = null)
    {
    }
}