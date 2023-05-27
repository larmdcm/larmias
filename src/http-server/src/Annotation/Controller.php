<?php

declare(strict_types=1);

namespace Larmias\HttpServer\Annotation;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Controller
{
    public function __construct(public string $prefix = '',public array $middleware = [])
    {
    }
}