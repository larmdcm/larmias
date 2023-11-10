<?php

declare(strict_types=1);

namespace Larmias\Database\Pool;

use Larmias\Database\Connections\Connection;
use Larmias\Database\Contracts\TransactionInterface;
use Larmias\Database\Exceptions\TransactionException;
use function Larmias\Support\throw_if;
use Throwable;

class Transaction implements TransactionInterface
{
    /**
     * @var TransactionInterface
     */
    protected TransactionInterface $transaction;

    /**
     * @var int
     */
    protected int $transCount = 0;

    /**
     * @param DbProxy $dbProxy
     */
    public function __construct(protected DbProxy $dbProxy)
    {
    }

    /**
     * 开启事务
     * @return void
     * @throws Throwable
     */
    public function beginTransaction(): void
    {
        $this->transCount++;
        if ($this->transCount === 1) {
            throw_if($this->getConnection()->inTransaction(), TransactionException::class, 'Already in transaction');
            $this->getConnection()->beginTransaction();
        }
    }

    /**
     * 提交事务
     * @return void
     * @throws Throwable
     */
    public function commit(): void
    {
        if ($this->transCount <= 0 || !$this->getConnection()->inTransaction()) {
            throw new TransactionException('Transaction committed or rolled back');
        }
        $this->transCount--;
        if ($this->transCount === 0) {
            try {
                $this->getConnection()->commit();
            } finally {
                $this->release();
            }
        }
    }

    /**
     * 回滚事务
     * @return void
     * @throws Throwable
     */
    public function rollback(): void
    {
        if ($this->transCount <= 0 || !$this->getConnection()->inTransaction()) {
            throw new TransactionException('Transaction committed or rolled back');
        }
        $this->transCount--;
        if ($this->transCount === 0) {
            try {
                $this->getConnection()->rollback();
            } finally {
                $this->release();
            }
        }
    }

    /**
     * 释放连接资源
     * @return void
     */
    public function release(): void
    {
        $connection = $this->getConnection();
        $this->dbProxy->getContext()->destroy($this->dbProxy->getTransactionContextKey());
        $this->dbProxy->getContext()->destroy($this->dbProxy->getContextKey());
        $connection->release();
    }

    /**
     * @return Connection
     */
    public function getConnection(): Connection
    {
        return $this->dbProxy->getContext()->remember($this->dbProxy->getContextKey(), function () {
            return $this->dbProxy->getConnection();
        });
    }
}