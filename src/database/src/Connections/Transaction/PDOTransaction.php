<?php

declare(strict_types=1);

namespace Larmias\Database\Connections\Transaction;

use Larmias\Database\Contracts\TransactionInterface;
use Larmias\Database\Connections\PDOConnection;
use Larmias\Database\Exceptions\TransactionException;
use function Larmias\Utils\throw_unless;

class PDOTransaction implements TransactionInterface
{
    /**
     * @param PDOConnection $connection
     */
    public function __construct(protected PDOConnection $connection)
    {
    }

    /**
     * 开启事务
     * @return TransactionInterface
     * @throws \Throwable
     */
    public function beginTransaction(): TransactionInterface
    {
        throw_unless($this->connection->getPdo()->beginTransaction(), TransactionException::class, 'Transaction begin failed.');
        return $this;
    }

    /**
     * 提交事务
     * @return void
     * @throws \Throwable
     */
    public function commit(): void
    {
        throw_unless($this->connection->getPdo()->commit(), TransactionException::class, 'Transaction commit failed.');
    }

    /**
     * 回滚事务
     * @return void
     * @throws \Throwable
     */
    public function rollback(): void
    {
        throw_unless($this->connection->getPdo()->rollBack(), TransactionException::class, 'Transaction rollback failed.');
    }
}