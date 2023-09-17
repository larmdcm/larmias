<?php

namespace Larmias\Engine\WorkerMan\WebSocket;

use Larmias\Contracts\Http\RequestInterface;
use Larmias\Contracts\WebSocket\ConnectionInterface;
use Larmias\Engine\WorkerMan\Http\Request;
use Larmias\Engine\WorkerMan\Tcp\Connection as BaseConnection;

class Connection extends BaseConnection implements ConnectionInterface
{
    /**
     * @var RequestInterface
     */
    protected RequestInterface $request;

    /**
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface
    {
        if (!isset($this->request)) {
            $this->request = new Request($this->connection->request);
        }

        return $this->request;
    }
}