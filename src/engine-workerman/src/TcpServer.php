<?php

declare(strict_types=1);

namespace Larmias\Engine\WorkerMan;

use Larmias\Engine\Event;
use Larmias\Engine\WorkerMan\Tcp\Connection;
use Workerman\Connection\TcpConnection;
use Throwable;

class TcpServer extends Server
{
    /**
     * @var string
     */
    protected string $protocol = 'tcp';

    public function onConnect(TcpConnection $workerConnection): void
    {
        try {
            $connection = new Connection($workerConnection);
            $this->trigger(Event::ON_CONNECT, [$connection]);
        } catch (Throwable $e) {
            $this->exceptionHandler($e);
        }
    }

    public function onMessage(TcpConnection $workerConnection, mixed $data): void
    {
        try {
            $connection = new Connection($workerConnection);
            $this->trigger(Event::ON_RECEIVE, [$connection, $data]);
        } catch (Throwable $e) {
            $this->exceptionHandler($e);
        }
    }

    public function onClose(TcpConnection $workerConnection): void
    {
        try {
            $connection = new Connection($workerConnection);
            $this->trigger(Event::ON_CLOSE, [$connection]);
        } catch (Throwable $e) {
            $this->exceptionHandler($e);
        }
    }
}