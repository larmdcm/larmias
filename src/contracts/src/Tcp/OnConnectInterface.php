<?php

declare(strict_types=1);

namespace Larmias\Contracts\Tcp;

interface OnConnectInterface
{
    /**
     * 连接进入事件
     * @param ConnectionInterface $connection
     * @return void
     */
    public function onConnect(ConnectionInterface $connection): void;
}