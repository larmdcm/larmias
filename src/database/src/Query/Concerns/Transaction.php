<?php

declare(strict_types=1);

namespace Larmias\Database\Query\Concerns;

use Larmias\Database\Contracts\TransactionInterface;
use Larmias\Database\Query\Builder;
use Closure;

/**
 * @mixin Builder
 */
trait Transaction
{
    /**
     * @return TransactionInterface
     */
    public function beginTransaction(): TransactionInterface
    {
        return $this->getConnection()->beginTransaction();
    }

    /**
     * @param Closure $callback
     * @return mixed
     */
    public function transaction(Closure $callback): mixed
    {
        return $this->getConnection()->transaction($callback);
    }
}