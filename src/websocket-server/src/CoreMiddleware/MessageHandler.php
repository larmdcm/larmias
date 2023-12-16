<?php

declare(strict_types=1);

namespace Larmias\WebSocketServer\CoreMiddleware;

use Closure;
use Larmias\Contracts\WebSocket\MessageHandlerInterface;
use Larmias\Contracts\WebSocket\FrameInterface;

class MessageHandler implements MessageHandlerInterface
{
    /**
     * @param Closure $handler
     */
    public function __construct(protected Closure $handler)
    {
    }

    /**
     * @param FrameInterface $frame
     * @return mixed
     */
    public function handle(FrameInterface $frame): mixed
    {
        return call_user_func($this->handler, $frame);
    }
}