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
     * 查询返回成功条数
     * @param string $sql
     * @param array $binds
     * @return int
     */
    public function execute(string $sql, array $binds = []): int;

    /**
     * 查询返回结果集
     * @param string $sql
     * @param array $binds
     * @return array
     */
    public function query(string $sql, array $binds = []): array;

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

    /**
     * 获取最后执行sql
     * @return string
     */
    public function getLastSql(): string;

    /**
     * 获取sql最后执行时间
     * @return float
     */
    public function getExecuteTime(): float;
}