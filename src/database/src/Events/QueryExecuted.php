<?php

declare(strict_types=1);

namespace Larmias\Database\Events;

class QueryExecuted
{
    /**
     * @param string $sql
     * @param array $bindings
     * @param float $time
     */
    public function __construct(public string $sql, public array $bindings = [], public float $time = 0.0)
    {
    }
}