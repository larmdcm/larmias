<?php

declare(strict_types=1);

namespace Larmias\Contracts\Network;

interface ConnectionInterface
{
    /**
     * 发送数据.
     * @param mixed $data
     * @return mixed
     */
    public function send(mixed $data): mixed;

    /**
     * 接收数据。
     * @return mixed
     */
    public function recv(): mixed;

    /**
     * 关闭连接.
     * @return bool
     */
    public function close(): bool;

    /**
     * 获取原生连接对象
     * @return mixed
     */
    public function getRawConnection(): mixed;
}