<?php

declare(strict_types=1);

namespace Larmias\HttpServer\Middleware;

use Closure;
use Larmias\Middleware\Middleware as BaseMiddleware;

class Middleware extends BaseMiddleware
{
    /**
     * @param \Closure $next
     * @return RequestHandler
     */
    public function warpNext(Closure $next): RequestHandler
    {
        return new RequestHandler($next);
    }
}