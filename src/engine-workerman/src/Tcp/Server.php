<?php

declare(strict_types=1);

namespace Larmias\Engine\WorkerMan\Tcp;

use Larmias\Engine\Event;
use Larmias\Engine\WorkerMan\Server as BaseServer;
use Workerman\Connection\TcpConnection;
use Throwable;

class Server extends BaseServer
{
    /**
     * @var string
     */
    protected string $protocol = 'tcp';

    /**
     * @param TcpConnection $tcpConnection
     * @return void
     */
    public function onConnect(TcpConnection $tcpConnection): void
    {
        try {
            $connection = new Connection($tcpConnection);
            $this->trigger(Event::ON_CONNECT, [$connection]);
        } catch (Throwable $e) {
            $this->handleException($e);
        }
    }

    /**
     * @param TcpConnection $tcpConnection
     * @param mixed $data
     * @return void
     */
    public function onMessage(TcpConnection $tcpConnection, mixed $data): void
    {
        try {
            $connection = new Connection($tcpConnection);
            $this->trigger(Event::ON_RECEIVE, [$connection, $data]);
        } catch (Throwable $e) {
            $this->handleException($e);
        }
    }

    /**
     * @param TcpConnection $tcpConnection
     * @return void
     */
    public function onClose(TcpConnection $tcpConnection): void
    {
        try {
            $connection = new Connection($tcpConnection);
            $this->trigger(Event::ON_CLOSE, [$connection]);
        } catch (Throwable $e) {
            $this->handleException($e);
        }
    }
}