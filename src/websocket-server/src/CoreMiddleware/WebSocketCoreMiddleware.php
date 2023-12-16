<?php

declare(strict_types=1);

namespace Larmias\WebSocketServer\CoreMiddleware;

class WebSocketCoreMiddleware extends CoreMiddleware
{
    /**
     * @var string|null
     */
    protected ?string $type = 'websocket';
}