<?php

declare(strict_types=1);

namespace Larmias\WebSocketServer;

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\WebSocket\OnOpenInterface;
use Larmias\Contracts\WebSocket\OnMessageInterface;
use Larmias\Contracts\WebSocket\OnCloseInterface;
use Larmias\Contracts\WebSocket\ConnectionInterface;
use Larmias\Contracts\WebSocket\FrameInterface;
use Larmias\WebSocketServer\Contracts\ConnectionManagerInterface;

class Server implements OnOpenInterface, OnMessageInterface, OnCloseInterface
{
    public function __construct(
        protected ContainerInterface         $container,
        protected ConnectionManagerInterface $connectionManager,
        protected HandlerManager             $handlerManager
    )
    {
    }

    /**
     * 连接打开事件
     * @param ConnectionInterface $connection
     * @return void
     */
    public function onOpen(ConnectionInterface $connection): void
    {
        $this->connectionManager->add($connection);
        $this->handlerManager->remember($connection->getId())->open();
    }

    /**
     * 接收消息事件
     * @param ConnectionInterface $connection
     * @param FrameInterface $frame
     * @return void
     */
    public function onMessage(ConnectionInterface $connection, FrameInterface $frame): void
    {
        $this->handlerManager->get($connection->getId())?->message($frame, $frame->getData());
    }

    /**
     * 连接关闭事件
     * @param ConnectionInterface $connection
     * @return void
     */
    public function onClose(ConnectionInterface $connection): void
    {
        $this->connectionManager->removeConnection($connection);
        $this->handlerManager->get($connection->getId())?->close();
    }
}