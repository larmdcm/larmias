<?php

declare(strict_types=1);

namespace Larmias\Database\Pool;

use Larmias\Database\Contracts\ConnectionInterface;
use Larmias\Database\Connections\Connection;

class DbProxy implements ConnectionInterface
{
    /**
     * @var DbPool
     */
    protected DbPool $pool;

    /**
     * @param array $config
     */
    public function __construct(protected array $config)
    {
        $this->pool = new DbPool($this->config['pool'] ?? [], $this->config);
    }

    /**
     * @return bool
     */
    public function connect(): bool
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    /**
     * @return bool
     */
    public function isConnected(): bool
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    /**
     * @return bool
     */
    public function close(): bool
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    /**
     * @param string $sql
     * @param array $binds
     * @return int
     */
    public function execute(string $sql, array $binds = []): int
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    /**
     * @param string $sql
     * @param array $binds
     * @return array
     */
    public function query(string $sql, array $binds = []): array
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    /**
     * @param string $sql
     * @param array $binds
     * @return string
     */
    public function buildSql(string $sql, array $binds = []): string
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    /**
     * @param string|null $name
     * @param mixed|null $default
     * @return mixed
     */
    public function getConfig(?string $name = null, mixed $default = null): mixed
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    /**
     * @return string
     */
    public function getLastSql(): string
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    /**
     * @return float
     */
    public function getExecuteTime(): float
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    /**
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public function call(string $method, array $arguments): mixed
    {
        $connection = $this->getConnection();
        try {
            return $connection->{$method}(...$arguments);
        } finally {
            $connection->release();
        }
    }

    /**
     * @return Connection
     */
    protected function getConnection(): Connection
    {
        /** @var Connection $connection */
        $connection = $this->pool->get();
        return $connection;
    }
}