<?php

declare(strict_types=1);

namespace Larmias\Database\Connections\Transaction;

use Larmias\Database\Contracts\TransactionInterface;
use Larmias\Database\Connections\PDOConnection;
use Larmias\Database\Events\TransactionBeginning;
use Larmias\Database\Events\TransactionCommitted;
use Larmias\Database\Events\TransactionRolledBack;
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
        $this->connection->getEventDispatcher()->dispatch(new TransactionBeginning($this->connection));
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
        $this->connection->getEventDispatcher()->dispatch(new TransactionCommitted($this->connection));
    }

    /**
     * 回滚事务
     * @return void
     * @throws \Throwable
     */
    public function rollback(): void
    {
        throw_unless($this->connection->getPdo()->rollBack(), TransactionException::class, 'Transaction rollback failed.');
        $this->connection->getEventDispatcher()->dispatch(new TransactionRolledBack($this->connection));
    }
}