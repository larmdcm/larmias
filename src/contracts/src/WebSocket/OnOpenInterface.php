<?php

declare(strict_types=1);

namespace Larmias\Contracts\WebSocket;

interface OnOpenInterface
{
    /**
     * @var string
     */
    public const ON_OPEN = 'onOpen';

    /**
     * 连接打开事件
     * @param ConnectionInterface $connection
     * @return void
     */
    public function onOpen(ConnectionInterface $connection): void;
}