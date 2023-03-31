<?php

declare(strict_types=1);

namespace Larmias\Database\Pool;

use Larmias\Database\Connections\Connection;
use Larmias\Database\Contracts\TransactionInterface;
use Larmias\Database\Exceptions\TransactionException;

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
        $this->beginTransaction();
    }

    /**
     * @return void
     */
    protected function beginTransaction(): void
    {
        $this->transCount++;
        if ($this->transCount === 1) {
            if ($this->getConnection()->inTransaction()) {
                throw new TransactionException('Already in transaction');
            }
            $this->transaction = $this->getConnection()->beginTransaction();
        }
    }

    /**
     * @return void
     */
    public function commit(): void
    {
        if ($this->transCount <= 0 || !$this->getConnection()->inTransaction()) {
            throw new TransactionException('Transaction committed or rolled back');
        }
        $this->transCount--;
        if ($this->transCount === 0) {
            try {
                $this->transaction->commit();
            } finally {
                $this->release();
            }
        }
    }

    /**
     * @return void
     */
    public function rollback(): void
    {
        if ($this->transCount <= 0 || !$this->getConnection()->inTransaction()) {
            throw new TransactionException('Transaction committed or rolled back');
        }
        $this->transCount--;
        if ($this->transCount === 0 && $this->getConnection()->inTransaction()) {
            try {
                $this->transaction->rollback();
            } finally {
                $this->release();
            }
        }
    }

    /**
     * @return void
     */
    public function release(): void
    {
        $this->dbProxy->getContext()->destroy($this->dbProxy->getTransactionContextKey());
        $this->dbProxy->getContext()->destroy($this->dbProxy->getContextKey());
        $this->getConnection()->release();
    }

    /**
     * @return Connection
     */
    public function getConnection(): Connection
    {
        return $this->dbProxy->getConnection(true);
    }
}