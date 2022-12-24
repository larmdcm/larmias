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

    public function onMessage(TcpConnection $workerConnection, mixed $data): void
    {
        try {
            $connection = new Connection($workerConnection);
            $this->trigger(Event::ON_RECEIVE, [$connection, $data]);
        } catch (Throwable $e) {
            $this->exceptionHandler($e);
        }
    }
}