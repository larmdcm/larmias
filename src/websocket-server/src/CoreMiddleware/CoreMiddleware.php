<?php

declare(strict_types=1);

namespace Larmias\WebSocketServer\CoreMiddleware;

use Closure;
use Larmias\Middleware\CoreMiddleware as BaseCoreMiddleware;

class CoreMiddleware extends BaseCoreMiddleware
{
    /**
     * @param Closure $handler
     * @return mixed
     */
    protected function wrapHandler(Closure $handler): mixed
    {
        return new MessageHandler($handler);
    }
}