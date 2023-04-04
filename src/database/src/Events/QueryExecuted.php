<?php

declare(strict_types=1);

namespace Larmias\Database\Events;

class QueryExecuted
{
    /**
     * @param string $sql
     * @param array $bindings
     * @param float $executedTime
     */
    public function __construct(public string $sql, public array $bindings = [], public float $executedTime = 0.0)
    {
    }
}