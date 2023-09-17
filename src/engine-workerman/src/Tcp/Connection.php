<?php

declare(strict_types=1);

namespace Larmias\Engine\WorkerMan\Tcp;

use Larmias\Contracts\Tcp\ConnectionInterface;
use Workerman\Connection\TcpConnection;

class Connection implements ConnectionInterface
{
    /**
     * @param TcpConnection $connection
     */
    public function __construct(protected TcpConnection $connection)
    {
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->connection->id;
    }

    /**
     * @return int
     */
    public function getFd(): int
    {
        return (int)$this->connection->getSocket();
    }

    /**
     * @return TcpConnection
     */
    public function getRawConnection(): TcpConnection
    {
        return $this->connection;
    }

    /**
     * @param mixed $data
     * @return bool
     */
    public function send(mixed $data): bool
    {
        return (bool)$this->connection->send($data);
    }

    /**
     * @return bool
     */
    public function close(): bool
    {
        $this->connection->close();
        return true;
    }
}