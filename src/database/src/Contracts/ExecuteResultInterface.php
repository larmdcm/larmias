<?php

declare(strict_types=1);

namespace Larmias\Database\Contracts;

interface ExecuteResultInterface
{
    /**
     * 获取执行sql
     * @return string
     */
    public function getExecuteSql(): string;

    /**
     * 获取执行绑定参数
     * @return array
     */
    public function getExecuteBindings(): array;

    /**
     * 获取执行时间
     * @return float
     */
    public function getExecuteTime(): float;

    /**
     * 获取影响行数
     * @return int
     */
    public function getRowCount(): int;

    /**
     * 获取查询结果集
     * @return array
     */
    public function getResultSet(): array;

    /**
     * 获取新增插入的ID
     * @return string|null
     */
    public function getInsertId(): ?string;
}