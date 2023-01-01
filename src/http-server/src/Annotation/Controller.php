<?php

declare(strict_types=1);

namespace Larmias\HttpServer\Annotation;

use Larmias\Di\Annotation\AnnotationAbstract;
use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Controller extends AnnotationAbstract
{
    public function __construct(public string $prefix = '',public array $middleware = [])
    {
    }
}