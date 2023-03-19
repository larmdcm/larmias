<?php

declare(strict_types=1);

namespace Larmias\Database\Contracts;

interface ConnectionInterface
{
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
     * 获取配置
     * @param string|null $name
     * @param mixed|null $default
     * @return mixed
     */
    public function getConfig(?string $name = null, mixed $default = null): mixed;
}