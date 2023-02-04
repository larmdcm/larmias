<?php

declare(strict_types=1);

namespace Larmias\SharedMemory;

use Larmias\Contracts\Tcp\ConnectionInterface;

class ConnectionManager
{
    /**
     * @var ConnectionInterface[]
     */
    protected static array $connections = [];

    public static function add(ConnectionInterface $connection): void
    {
        static::$connections[$connection->getId()] = $connection;
    }

    public static function get(int $id): ?ConnectionInterface
    {
        return static::$connections[$id] ?? null;
    }

    public static function has(int $id): bool
    {
        return isset(static::$connections[$id]);
    }

    public static function remove(ConnectionInterface $connection): void
    {
        $id = $connection->getId();
        if (isset(static::$connections[$id])) {
            unset(static::$connections[$id]);
        }
    }

    public static function count(): int
    {
        return \count(static::$connections);
    }
}