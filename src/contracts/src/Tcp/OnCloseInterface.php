<?php

declare(strict_types=1);

namespace Larmias\Contracts\Tcp;

interface OnCloseInterface
{
    /**
     * 连接关闭事件
     * @param ConnectionInterface $connection
     * @return void
     */
    public function onClose(ConnectionInterface $connection): void;
}