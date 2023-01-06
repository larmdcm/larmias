<?php

declare(strict_types=1);

namespace Larmias\HttpServer\Middleware;

use Closure;
use Larmias\Middleware\Middleware as BaseMiddleware;

class Middleware extends BaseMiddleware
{
    /**
     * @param Closure $handler
     * @return RequestHandler
     */
    public function warpHandler(Closure $handler): RequestHandler
    {
        return new RequestHandler($handler);
    }
}