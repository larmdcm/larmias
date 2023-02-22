<?php

declare(strict_types=1);

namespace Larmias\SharedMemory;

use Larmias\Contracts\Tcp\ConnectionInterface;

class Context
{
    public const KEY_STORE_SELECT = 'storeSelect';

    protected static int $id = 0;

    protected static array $data = [];

    protected static ConnectionInterface $connection;

    public static function getStoreSelect(): string
    {
        return static::getData(self::KEY_STORE_SELECT, 'default');
    }

    public static function setStoreSelect(string $select): void
    {
        static::setData(self::KEY_STORE_SELECT, $select);
    }

    public static function getConnection(): ConnectionInterface
    {
        return static::$connection;
    }

    public static function setConnection(ConnectionInterface $connection): void
    {
        static::$connection = $connection;
        static::$id = $connection->getId();
    }

    public static function setData(string $name, mixed $value): void
    {
        $id = static::getId();
        if (!isset(static::$data[$id])) {
            static::$data[$id] = [];
        }
        static::$data[$id][$name] = $value;
    }

    public static function getData(string $name, mixed $default = null): mixed
    {
        $id = static::getId();
        $data = static::$data[$id] ?? [];
        return $data[$name] ?? $default;
    }

    public static function clear(int $id): void
    {
        unset(static::$data[$id]);
    }

    public static function setId(int $id): void
    {
        static::$id = $id;
    }

    public static function getId(): int
    {
        return static::$id;
    }
}