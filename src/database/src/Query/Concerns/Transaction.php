<?php

declare(strict_types=1);

namespace Larmias\Database\Query\Concerns;

use Larmias\Database\Query\BaseQuery;
use Closure;

/**
 * @mixin BaseQuery
 */
trait Transaction
{
    /**
     * 开启事务
     * @return void
     */
    public function beginTransaction(): void
    {
        $this->getConnection()->beginTransaction();
    }

    /**
     * 事务提交
     * @return void
     */
    public function commit(): void
    {
        $this->getConnection()->commit();
    }

    /**
     * 事务回滚
     * @return void
     */
    public function rollback(): void
    {
        $this->getConnection()->rollback();
    }

    /**
     * 事务处理回调
     * @param Closure $callback
     * @return mixed
     */
    public function transaction(Closure $callback): mixed
    {
        return $this->getConnection()->transaction($callback);
    }
}