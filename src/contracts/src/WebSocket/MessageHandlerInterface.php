<?php

declare(strict_types=1);

namespace Larmias\Contracts\WebSocket;

interface MessageHandlerInterface
{
    /**
     * @param FrameInterface $frame
     * @return mixed
     */
    public function handle(FrameInterface $frame): mixed;
}