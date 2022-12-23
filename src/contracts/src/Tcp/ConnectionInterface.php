<?php

declare(strict_types=1);

namespace Larmias\Contracts\Tcp;

interface ConnectionInterface
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