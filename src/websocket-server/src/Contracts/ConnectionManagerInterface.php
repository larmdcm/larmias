<?php

declare(strict_types=1);

namespace Larmias\WebSocketServer\Contracts;

use Larmias\Contracts\WebSocket\ConnectionInterface;

interface ConnectionManagerInterface
{
    /**
     * @param ConnectionInterface $connection
     * @return void
     */
    public function add(ConnectionInterface $connection): void;

    /**
     * @param int $id
     * @return ConnectionInterface|null
     */
    public function get(int $id): ?ConnectionInterface;

    /**
     * @param int $id
     * @return bool
     */
    public function exists(int $id): bool;

    /**
     * @param int $id
     * @return void
     */
    public function remove(int $id): void;

    /**
     * @param ConnectionInterface $connection
     * @return void
     */
    public function removeConnection(ConnectionInterface $connection): void;

    /**
     * @return int
     */
    public function count(): int;
}