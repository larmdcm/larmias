<?php

declare(strict_types=1);

namespace Larmias\Engine\WorkerMan\Tcp;

use Larmias\Engine\Event;
use Workerman\Connection\TcpConnection;
use Throwable;
use Larmias\Engine\WorkerMan\Server as BaseServer;

class Server extends BaseServer
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