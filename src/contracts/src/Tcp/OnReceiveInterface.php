<?php

declare(strict_types=1);

namespace Larmias\Contracts\Tcp;

interface OnReceiveInterface
{
    /**
     * 接收数据事件
     * @param ConnectionInterface $connection
     * @param mixed $data
     * @return void
     */
    public function onReceive(ConnectionInterface $connection, mixed $data): void;
}