<?php

declare(strict_types=1);

namespace Larmias\Engine\WorkerMan\Tcp;

use Larmias\Contracts\Tcp\ConnectionInterface;
use Workerman\Connection\TcpConnection;

class Connection implements ConnectionInterface
{
    public function __construct(protected TcpConnection $connection)
    {

    }

    public function getId(): int
    {
        return $this->connection->id;
    }

    public function getRawConnection(): object
    {
        return $this->connection;
    }
}