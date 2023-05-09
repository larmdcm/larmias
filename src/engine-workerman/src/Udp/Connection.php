<?php

declare(strict_types=1);

namespace Larmias\Engine\WorkerMan\Udp;

use Larmias\Contracts\Udp\ConnectionInterface;
use Workerman\Connection\UdpConnection;

class Connection implements ConnectionInterface
{
    /**
     * @param UdpConnection $connection
     */
    public function __construct(protected UdpConnection $connection)
    {
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
     * @param mixed $data
     * @return bool
     */
    public function close(mixed $data = null): bool
    {
        return $this->connection->close($data);
    }

    /**
     * @return object
     */
    public function getRawConnection(): object
    {
        return $this->connection;
    }
}