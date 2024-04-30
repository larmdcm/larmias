<?php

declare(strict_types=1);

namespace Larmias\Database\Query\Concerns;

use Larmias\Database\Query\BaseQuery;
use Closure;
use Larmias\Database\Query\Contracts\JoinClauseInterface;
use Larmias\Database\Query\JoinClause;

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
        if ($condition instanceof Closure) {
            $on = $this->parseJoinClosure($condition);
        } else {
            $on = $condition;
        }

        $this->options['join'][] = [$table, $on, $joinType];
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

    /**
     * 解析join闭包
     * @param Closure $callback
     * @return Closure
     */
    protected function parseJoinClosure(Closure $callback): Closure
    {
        return function () use ($callback): JoinClauseInterface {
            $joinClause = new JoinClause();
            $joinClause->setConnection($this->connection);
            $joinClause->setBuilder($this->newBuilder());
            $result = $callback($joinClause);

            if ($result instanceof JoinClauseInterface) {
                $joinClause = $result;
            }

            return $joinClause;
        };
    }
}