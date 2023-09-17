<?php

declare(strict_types=1);

namespace Larmias\Contracts\WebSocket;

interface OnMessageInterface
{
    /**
     * 接收消息事件
     * @param ConnectionInterface $connection
     * @param FrameInterface $frame
     * @return void
     */
    public function onMessage(ConnectionInterface $connection, FrameInterface $frame): void;
}