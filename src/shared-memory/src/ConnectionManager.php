<?php

declare(strict_types=1);

namespace Larmias\SharedMemory;

use Larmias\Contracts\Tcp\ConnectionInterface;
use function count;

class ConnectionManager
{
    /**
     * @var ConnectionInterface[]
     */
    protected static array $connections = [];

    /**
     * @param ConnectionInterface $connection
     * @return void
     */
    public static function add(ConnectionInterface $connection): void
    {
        static::$connections[$connection->getId()] = $connection;
    }

    /**
     * @param int $id
     * @return ConnectionInterface|null
     */
    public static function get(int $id): ?ConnectionInterface
    {
        return static::$connections[$id] ?? null;
    }

    /**
     * @param int $id
     * @return bool
     */
    public static function has(int $id): bool
    {
        return isset(static::$connections[$id]);
    }

    /**
     * @param ConnectionInterface $connection
     * @return void
     */
    public static function remove(ConnectionInterface $connection): void
    {
        $id = $connection->getId();
        if (isset(static::$connections[$id])) {
            unset(static::$connections[$id]);
        }
    }

    /**
     * @return int
     */
    public static function count(): int
    {
        return count(static::$connections);
    }
}