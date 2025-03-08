<?php

declare(strict_types=1);

namespace Larmias\Database\Pool;

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\ContextInterface;
use Larmias\Database\Contracts\ConnectionInterface;
use Larmias\Database\Connections\Connection;
use Larmias\Database\Contracts\ExecuteResultInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Throwable;
use Closure;

class DbProxy implements ConnectionInterface
{
    /**
     * @var DbPool
     */
    protected DbPool $pool;

    /**
     * @var ContextInterface
     */
    protected ContextInterface $context;

    /**
     * @var EventDispatcherInterface
     */
    protected EventDispatcherInterface $eventDispatcher;

    /**
     * @param ContainerInterface $container
     * @param array $config
     * @throws Throwable
     */
    public function __construct(ContainerInterface $container, protected array $config)
    {
        $this->pool = new DbPool($container, $this->config['pool'] ?? [], $this->config);
        $this->context = $container->get(ContextInterface::class);
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
     * @param array $bindings
     * @return ExecuteResultInterface
     */
    public function execute(string $sql, array $bindings = []): ExecuteResultInterface
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    /**
     * @param string $sql
     * @param array $bindings
     * @return ExecuteResultInterface
     */
    public function query(string $sql, array $bindings = []): ExecuteResultInterface
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    /**
     * @param string $sql
     * @param array $bindings
     * @return string
     */
    public function buildSql(string $sql, array $bindings = []): string
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
     * 开启事务
     * @return void
     * @throws Throwable
     */
    public function beginTransaction(): void
    {
        /** @var Transaction $transaction */
        $transaction = $this->context->remember($this->getTransactionContextKey(), function () {
            return new Transaction($this);
        });

        $transaction->beginTransaction();
    }

    /**
     * 提交事务
     * @return void
     * @throws Throwable
     */
    public function commit(): void
    {
        /** @var Transaction $transaction */
        $transaction = $this->context->get($this->getTransactionContextKey());
        $transaction->commit();
    }


    /**
     * 回滚事务
     * @return void
     * @throws Throwable
     */
    public function rollback(): void
    {
        /** @var Transaction $transaction */
        $transaction = $this->context->get($this->getTransactionContextKey());
        $transaction->rollback();
    }


    /**
     * @param Closure $callback
     * @return mixed
     * @throws Throwable
     */
    public function transaction(Closure $callback): mixed
    {
        $this->beginTransaction();
        try {
            $result = $callback();
            $this->commit();
            return $result;
        } catch (Throwable $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * @return bool
     */
    public function inTransaction(): bool
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    /**
     * 获取数据表信息
     * @param string $table
     * @param bool $force
     * @return array
     */
    public function getSchemaInfo(string $table, bool $force = false): array
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
            if (!$this->context->has($this->getContextKey())) {
                $connection->release();
            }
        }
    }

    /**
     * @return ContextInterface
     */
    public function getContext(): ContextInterface
    {
        return $this->context;
    }

    /**
     * @return Connection
     */
    public function getConnection(): Connection
    {
        $contextKey = $this->getContextKey();
        if ($this->context->has($contextKey)) {
            return $this->context->get($contextKey);
        }
        /** @var Connection $connection */
        $connection = $this->pool->get();

        $connection->setEventDispatcher($this->getEventDispatcher());

        return $connection;
    }

    /**
     * @return string
     */
    public function getContextKey(): string
    {
        return 'db.connections.' . $this->config['name'];
    }

    /**
     * @return string
     */
    public function getTransactionContextKey(): string
    {
        return 'db.transactions.' . $this->config['name'];
    }

    /**
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher(): EventDispatcherInterface
    {
        return $this->eventDispatcher;
    }

    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @return void
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }
}