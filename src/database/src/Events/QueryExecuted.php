<?php

declare(strict_types=1);

namespace Larmias\Database\Events;

use Larmias\Database\Contracts\ConnectionInterface;

class QueryExecuted
{
    /**
     * @param ConnectionInterface $connection
     * @param string $sql
     * @param array $bindings
     * @param float $time
     */
    public function __construct(public ConnectionInterface $connection, public string $sql, public array $bindings = [], public float $time = 0.0)
    {
    }
}