<?php

declare(strict_types=1);

namespace Larmias\HttpServer\Annotation;

use Larmias\Di\Annotation\AbstractAnnotation;
use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Middleware extends AbstractAnnotation
{
    public array $middleware = [];

    public function __construct(...$middleware)
    {
        $this->middleware = $middleware;
    }
}