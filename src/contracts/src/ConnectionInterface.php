<?php

declare(strict_types=1);

namespace Larmias\Contracts;

interface ConnectionInterface
{
    /**
     * 发送数据.
     *
     * @param mixed $data
     * @return mixed
     */
    public function send(mixed $data): mixed;

    /**
     * 关闭连接.
     *
     * @param mixed $data
     * @return bool
     */
    public function close(mixed $data = null): bool;

    /**
     * 获取原生连接对象
     *
     * @return object
     */
    public function getRawConnection(): object;
}