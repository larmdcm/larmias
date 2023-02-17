<?php

declare(strict_types=1);

namespace Larmias\Engine\WorkerMan\WebSocket;

use Larmias\Engine\Event;
use Larmias\Engine\WorkerMan\Server as BaseServer;
use Larmias\Engine\WorkerMan\Tcp\Connection;
use Workerman\Connection\TcpConnection;
use Throwable;

class Server extends BaseServer
{
    /**
     * @var string
     */
    protected string $protocol = 'websocket';

    /**
     * @param TcpConnection $workerConnection
     * @param mixed $data
     * @return void
     */
    public function onWebSocketConnect(TcpConnection $workerConnection, mixed $data): void
    {
        try {
            $connection = new Connection($workerConnection);
            $this->trigger(Event::ON_OPEN, [$connection, $data]);
        } catch (Throwable $e) {
            $this->exceptionHandler($e);
        }
    }

    /**
     * @param TcpConnection $workerConnection
     * @param mixed $data
     * @return void
     */
    public function onMessage(TcpConnection $workerConnection, mixed $data): void
    {
        try {
            $connection = new Connection($workerConnection);
            $this->trigger(Event::ON_MESSAGE, [$connection, $data]);
        } catch (Throwable $e) {
            $this->exceptionHandler($e);
        }
    }

    /**
     * @param TcpConnection $workerConnection
     * @return void
     */
    public function onWebSocketClose(TcpConnection $workerConnection): void
    {
        try {
            $connection = new Connection($workerConnection);
            $this->trigger(Event::ON_CLOSE, [$connection]);
        } catch (Throwable $e) {
            $this->exceptionHandler($e);
        }
    }
}