<?php

declare(strict_types=1);

namespace Larmias\HttpServer\Routing;

use Larmias\Middleware\Middleware as BaseMiddleware;

class Middleware extends BaseMiddleware
{
    /**
     * @var string|null
     */
    protected ?string $type = 'http_route';
}