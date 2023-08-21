<?php

declare(strict_types=1);

namespace Larmias\Database\Events;

use Larmias\Database\Contracts\ConnectionInterface;
use PDOStatement;

class StatementPrepared
{
    /**
     * @param ConnectionInterface $connection
     * @param PDOStatement $statement
     */
    public function __construct(public ConnectionInterface $connection, public PDOStatement $statement)
    {
    }
}