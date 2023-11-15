<?php

declare(strict_types=1);

namespace Larmias\HttpServer\CoreMiddleware;

use Closure;
use Larmias\Middleware\CoreMiddleware as BaseCoreMiddleware;

class CoreMiddleware extends BaseCoreMiddleware
{
    /**
     * @param Closure $handler
     * @return RequestHandler
     */
    public function wrapHandler(Closure $handler): RequestHandler
    {
        return new RequestHandler($handler);
    }
}