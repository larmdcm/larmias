<?php

declare(strict_types=1);

namespace Larmias\Database\Contracts;

use Closure;
use Psr\EventDispatcher\EventDispatcherInterface;

interface ConnectionInterface extends TransactionInterface
{
    /**
     * @return bool
     */
    public function connect(): bool;

    /**
     * @return bool
     */
    public function isConnected(): bool;

    /**
     * @return bool
     */
    public function close(): bool;

    /**
     * @param Closure $callback
     * @return mixed
     */
    public function transaction(Closure $callback): mixed;

    /**
     * @return bool
     */
    public function inTransaction(): bool;

    /**
     * 执行语句
     * @param string $sql
     * @param array $bindings
     * @return ExecuteResultInterface
     */
    public function execute(string $sql, array $bindings = []): ExecuteResultInterface;

    /**
     * 查询结果集
     * @param string $sql
     * @param array $bindings
     * @return ExecuteResultInterface
     */
    public function query(string $sql, array $bindings = []): ExecuteResultInterface;

    /**
     * 构建sql
     * @param string $sql
     * @param array $bindings
     * @return string
     */
    public function buildSql(string $sql, array $bindings = []): string;

    /**
     * 获取配置
     * @param string|null $name
     * @param mixed|null $default
     * @return mixed
     */
    public function getConfig(?string $name = null, mixed $default = null): mixed;

    /**
     * 获取数据表信息
     * @param string $table
     * @param bool $force
     * @return array
     */
    public function getSchemaInfo(string $table, bool $force = false): array;

    /**
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher(): EventDispatcherInterface;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @return void
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): void;
}