<?php

declare(strict_types=1);

namespace Larmias\Database\Query\Concerns;

use Larmias\Database\Contracts\TransactionInterface;
use Larmias\Database\Query\QueryBuilder;
use Closure;

/**
 * @mixin QueryBuilder
 */
trait Transaction
{
    /**
     * 开启事务
     * @return TransactionInterface
     */
    public function beginTransaction(): TransactionInterface
    {
        return $this->getConnection()->beginTransaction();
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