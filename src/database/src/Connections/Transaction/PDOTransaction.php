<?php

declare(strict_types=1);

namespace Larmias\Database\Connections\Transaction;

use Larmias\Database\Contracts\TransactionInterface;
use Larmias\Database\Connections\PDOConnection;
use Larmias\Database\Exceptions\TransactionException;

class PDOTransaction implements TransactionInterface
{
    /**
     * @param PDOConnection $connection
     */
    public function __construct(protected PDOConnection $connection)
    {
    }

    /**
     * @return TransactionInterface
     */
    public function beginTransaction(): TransactionInterface
    {
        if (!$this->connection->getPdo()->beginTransaction()) {
            throw new TransactionException('Transaction begin failed');
        }

        return $this;
    }

    /**
     * @return void
     */
    public function commit(): void
    {
        if (!$this->connection->getPdo()->commit()) {
            throw new TransactionException('Transaction commit failed');
        }
    }

    /**
     * @return void
     */
    public function rollback(): void
    {
        if (!$this->connection->getPdo()->rollBack()) {
            throw new TransactionException('Transaction rollback failed');
        }
    }
}