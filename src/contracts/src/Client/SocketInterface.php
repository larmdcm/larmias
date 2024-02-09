<?php

declare(strict_types=1);

namespace Larmias\Contracts\Client;

interface SocketInterface extends BaseSocketInterface
{
    /**
     * 接收数据。
     * @param int $length
     * @return mixed
     */
    public function recv(int $length = 65535): mixed;
}