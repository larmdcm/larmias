<?php

declare(strict_types=1);

namespace Larmias\SharedMemory;

use Larmias\Contracts\ContextInterface;
use Larmias\Contracts\Tcp\ConnectionInterface;
use Larmias\SharedMemory\Exceptions\ServerException;
use function Larmias\Support\throw_unless;

class Context
{
    /**
     * @var array
     */
    protected static array $data = [];

    /**
     * @var ContextInterface
     */
    protected static ContextInterface $context;

    /**
     * @param ContextInterface $context
     * @return void
     */
    public static function init(ContextInterface $context): void
    {
        static::$context = $context;
    }

    /**
     * @return string
     * @throws \Throwable
     */
    public static function getStoreSelect(): string
    {
        return static::getConnectionData('store.select', 'default');
    }

    /**
     * @param string $select
     * @return void
     * @throws \Throwable
     */
    public static function setStoreSelect(string $select): void
    {
        static::setConnectionData('store.select', $select);
    }

    /**
     * @return ConnectionInterface
     * @throws \Throwable
     */
    public static function getConnection(): ConnectionInterface
    {
        $id = static::getConnectId();
        $connection = ConnectionManager::get($id);
        throw_unless($connection, ServerException::class, sprintf('Connection id does not exist: %d', $id));
        return $connection;
    }

    /**
     * @return int
     * @throws \Throwable
     */
    public static function getConnectId(): int
    {
        $id = static::$context->get('shared_memory.conn.id');
        throw_unless($id, ServerException::class, sprintf('Connection id does not exist: %d', $id));
        return $id;
    }

    /**
     * @param int $id
     * @return void
     */
    public static function setConnectId(int $id): void
    {
        static::$context->set('shared_memory.conn.id', $id);
    }

    /**
     * @param string $name
     * @param mixed|null $default
     * @return mixed
     * @throws \Throwable
     */
    public static function getConnectionData(string $name, mixed $default = null): mixed
    {
        $id = static::getConnectId();
        $data = static::$data[$id] ?? [];
        return $data[$name] ?? $default;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return void
     * @throws \Throwable
     */
    public static function setConnectionData(string $name, mixed $value): void
    {
        $id = static::getConnectId();
        if (!isset(static::$data[$id])) {
            static::$data[$id] = [];
        }
        static::$data[$id][$name] = $value;
    }

    /**
     * @param int $id
     * @return void
     */
    public static function clearConnectionData(int $id): void
    {
        unset(static::$data[$id]);
    }
}