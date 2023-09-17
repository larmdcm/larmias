<?php

declare(strict_types=1);

namespace Larmias\Contracts\WebSocket;

use Larmias\Contracts\Http\RequestInterface;
use Larmias\Contracts\Tcp\ConnectionInterface as BaseConnectionInterface;

interface ConnectionInterface extends BaseConnectionInterface
{
    /**
     * 获取请求对象
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface;
}