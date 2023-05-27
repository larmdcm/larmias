<?php

declare(strict_types=1);

namespace Larmias\HttpServer\Annotation;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Middleware
{
    /**
     * @var array
     */
    public array $middleware = [];

    /**
     * @param ...$middleware
     */
    public function __construct(...$middleware)
    {
        $this->middleware = $middleware;
    }
}