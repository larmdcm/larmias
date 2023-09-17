<?php

declare(strict_types=1);

namespace Larmias\Contracts\Udp;

interface OnPacketInterface
{
    /**
     * 接收数据事件
     * @param ConnectionInterface $connection
     * @param mixed $data
     * @return void
     */
    public function onPacket(ConnectionInterface $connection, mixed $data): void;
}