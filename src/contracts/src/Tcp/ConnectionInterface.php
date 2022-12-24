<?php

declare(strict_types=1);

namespace Larmias\Contracts\Tcp;

use Larmias\Contracts\ConnectionInterface as BaseConnectionInterface;

interface ConnectionInterface extends BaseConnectionInterface
{
    /**
     * 获取连接id
     *
     * @return int
     */
    public function getId(): int;

    /**
     * 获取原生连接对象
     *
     * @return object
     */
    public function getRawConnection(): object;
}