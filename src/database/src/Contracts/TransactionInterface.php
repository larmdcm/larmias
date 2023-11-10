<?php

declare(strict_types=1);

namespace Larmias\Database\Contracts;

interface TransactionInterface
{
    /**
     * 开启事务
     * @return void
     */
    public function beginTransaction(): void;

    /**
     * 事务提交
     * @return void
     */
    public function commit(): void;

    /**
     * 事务回滚
     * @return void
     */
    public function rollback(): void;
}