<?php

declare(strict_types=1);

namespace Larmias\WebSocketServer;

use Larmias\Contracts\WebSocket\ConnectionInterface;
use Larmias\WebSocketServer\Contracts\ConnectionManagerInterface;

class ConnectionManager implements ConnectionManagerInterface
{
    /**
     * @var ConnectionInterface[]
     */
    protected array $connections = [];

    /**
     * @param ConnectionInterface $connection
     * @return void
     */
    public function add(ConnectionInterface $connection): void
    {
        $this->connections[$connection->getId()] = $connection;
    }

    /**
     * @param int $id
     * @return ConnectionInterface|null
     */
    public function get(int $id): ?ConnectionInterface
    {
        return $this->connections[$id] ?? null;
    }

    /**
     * @param int $id
     * @return bool
     */
    public function exists(int $id): bool
    {
        return isset($this->connections[$id]);
    }

    /**
     * @param int $id
     * @return void
     */
    public function remove(int $id): void
    {
        if (isset($this->connections[$id])) {
            unset($this->connections[$id]);
        }
    }

    /**
     * @param ConnectionInterface $connection
     * @return void
     */
    public function removeConnection(ConnectionInterface $connection): void
    {
        static::remove($connection->getId());
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->connections);
    }
}