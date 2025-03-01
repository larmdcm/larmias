<?php

declare(strict_types=1);

namespace Larmias\Engine\WorkerMan\Udp;

use Larmias\Contracts\Udp\ConnectionInterface;
use Workerman\Connection\UdpConnection;
use BadMethodCallException;

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
     * @return bool
     */
    public function close(): bool
    {
        $this->connection->close();
        return true;
    }

    /**
     * @return object
     */
    public function getRawConnection(): object
    {
        return $this->connection;
    }

    /**
     * @return mixed
     */
    public function recv(): mixed
    {
        throw new BadMethodCallException(__FUNCTION__ . ' not implement.');
    }
}