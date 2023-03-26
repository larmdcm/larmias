<?php

declare(strict_types=1);

namespace Larmias\Database\Contracts;

interface ConnectionInterface
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
     * 查询
     * @param string $sql
     * @param array $binds
     * @return ExecuteResultInterface
     */
    public function execute(string $sql, array $binds = []): ExecuteResultInterface;

    /**
     * 查询结果集
     * @param string $sql
     * @param array $binds
     * @return ExecuteResultInterface
     */
    public function query(string $sql, array $binds = []): ExecuteResultInterface;

    /**
     * 构建sql
     * @param string $sql
     * @param array $binds
     * @return string
     */
    public function buildSql(string $sql, array $binds = []): string;

    /**
     * 获取配置
     * @param string|null $name
     * @param mixed|null $default
     * @return mixed
     */
    public function getConfig(?string $name = null, mixed $default = null): mixed;
}