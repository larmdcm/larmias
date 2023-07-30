<?php

declare(strict_types=1);

namespace Larmias\Database\Query\Concerns;

use Larmias\Database\Query\BaseQuery;

/**
 * @mixin BaseQuery
 */
trait JoinQuery
{
    /**
     * JOIN查询
     * @param array|string $table
     * @param mixed $condition
     * @param string $joinType
     * @return static
     */
    public function join(array|string $table, mixed $condition, string $joinType = 'INNER'): static
    {
        $this->options['join'][] = [$table, $condition, $joinType];
        return $this;
    }

    /**
     * INNER JOIN查询
     * @param array|string $table
     * @param mixed $condition
     * @return static
     */
    public function innerJoin(array|string $table, mixed $condition): static
    {
        return $this->join($table, $condition);
    }

    /**
     * LEFT JOIN查询
     * @param array|string $table
     * @param mixed $condition
     * @return static
     */
    public function leftJoin(array|string $table, mixed $condition): static
    {
        return $this->join($table, $condition, 'LEFT');
    }

    /**
     * RIGHT JOIN查询
     * @param array|string $table
     * @param mixed $condition
     * @return static
     */
    public function rightJoin(array|string $table, mixed $condition): static
    {
        return $this->join($table, $condition, 'RIGHT');
    }
}