<?php

declare(strict_types=1);

namespace Larmias\WebSocketServer;

use Larmias\Contracts\WebSocket\OnOpenInterface;
use Larmias\Contracts\WebSocket\OnMessageInterface;
use Larmias\Contracts\WebSocket\OnCloseInterface;
use Larmias\Contracts\WebSocket\ConnectionInterface;
use Larmias\Contracts\WebSocket\FrameInterface;

class Server implements OnOpenInterface, OnMessageInterface, OnCloseInterface
{
    public function __construct()
    {
    }

    /**
     * 连接打开事件
     * @param ConnectionInterface $connection
     * @return void
     */
    public function onOpen(ConnectionInterface $connection): void
    {
        ConnectionManager::add($connection);
    }

    /**
     * 接收消息事件
     * @param ConnectionInterface $connection
     * @param FrameInterface $frame
     * @return void
     */
    public function onMessage(ConnectionInterface $connection, FrameInterface $frame): void
    {
    }

    /**
     * 连接关闭事件
     * @param ConnectionInterface $connection
     * @return void
     */
    public function onClose(ConnectionInterface $connection): void
    {
        ConnectionManager::remove($connection);
    }
}