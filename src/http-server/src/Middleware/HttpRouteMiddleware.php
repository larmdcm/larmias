<?php

declare(strict_types=1);

namespace Larmias\HttpServer\Middleware;

class HttpRouteMiddleware extends Middleware
{
    /**
     * @var string|null
     */
    protected ?string $type = 'http_route';
}