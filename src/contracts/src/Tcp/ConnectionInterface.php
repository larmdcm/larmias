<?php

declare(strict_types=1);

namespace Larmias\Contracts\Tcp;

use Larmias\Contracts\Network\ConnectionInterface as BaseConnectionInterface;

interface ConnectionInterface extends BaseConnectionInterface
{
    /**
     * 获取连接id
     * @return int
     */
    public function getId(): int;

    /**
     * 获取连接的文件描述符
     * @return int
     */
    public function getFd(): int;
}