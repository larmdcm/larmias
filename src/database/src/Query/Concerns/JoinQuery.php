<?php

declare(strict_types=1);

namespace Larmias\Database\Query\Concerns;

use Larmias\Database\Contracts\QueryInterface;
use Larmias\Database\Query\QueryBuilder;

/**
 * @mixin QueryBuilder
 */
trait JoinQuery
{
    /**
     * JOIN查询
     * @param array|string $table
     * @param mixed $condition
     * @param string $joinType
     * @return QueryInterface
     */
    public function join(array|string $table, mixed $condition, string $joinType = 'INNER'): QueryInterface
    {
        $this->options['join'][] = [$table, $condition, $joinType];
        return $this;
    }

    /**
     * INNER JOIN查询
     * @param array|string $table
     * @param mixed $condition
     * @return QueryInterface
     */
    public function innerJoin(array|string $table, mixed $condition): QueryInterface
    {
        return $this->join($table, $condition);
    }

    /**
     * LEFT JOIN查询
     * @param array|string $table
     * @param mixed $condition
     * @return QueryInterface
     */
    public function leftJoin(array|string $table, mixed $condition): QueryInterface
    {
        return $this->join($table, $condition, 'LEFT');
    }

    /**
     * RIGHT JOIN查询
     * @param array|string $table
     * @param mixed $condition
     * @return QueryInterface
     */
    public function rightJoin(array|string $table, mixed $condition): QueryInterface
    {
        return $this->join($table, $condition, 'RIGHT');
    }
}