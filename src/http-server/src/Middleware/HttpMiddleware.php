<?php

declare(strict_types=1);

namespace Larmias\HttpServer\Middleware;

class HttpMiddleware extends Middleware
{
    /**
     * @var string|null
     */
    protected ?string $type = 'http';
}