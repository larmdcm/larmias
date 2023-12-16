<?php

declare(strict_types=1);

namespace Larmias\Contracts\WebSocket;

interface MiddlewareInterface
{
    /**
     * @param FrameInterface $frame
     * @param MessageHandlerInterface $handler
     * @return mixed
     */
    public function process(FrameInterface $frame, MessageHandlerInterface $handler): mixed;
}