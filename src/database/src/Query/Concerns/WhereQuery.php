<?php

declare(strict_types=1);

namespace Larmias\Database\Query\Concerns;

use Larmias\Database\Query\Builder;
use Larmias\Database\Contracts\QueryInterface;
use Larmias\Database\Contracts\ExpressionInterface;
use Closure;
use function strtoupper;
use function is_array;

/**
 * @mixin Builder
 */
trait WhereQuery
{
    /**
     * @param mixed $field
     * @param mixed|null $op
     * @param mixed|null $value
     * @param string $logic
     * @return QueryInterface
     */
    public function where(mixed $field, mixed $op = null, mixed $value = null, string $logic = 'AND'): QueryInterface
    {
        if (is_array($field)) {
            $conditions = $this->parseWhereArray($field);
        } else if ($field instanceof Closure) {
            $conditions = [$this->parseWhereClosure($field)];
        } else if ($field instanceof ExpressionInterface) {
            $conditions = [$field];
        } else {
            $conditions = [$this->parseWhereOp($field, $op, $value)];
        }

        foreach ($conditions as $condition) {
            $this->options['where'][strtoupper($logic)][] = $condition;
        }

        return $this;
    }

    /**
     * @param mixed $field
     * @param mixed|null $op
     * @param mixed|null $value
     * @return QueryInterface
     */
    public function orWhere(mixed $field, mixed $op = null, mixed $value = null): QueryInterface
    {
        return $this->where($field, $op, $value, 'OR');
    }

    /**
     * @param string $field
     * @param string $logic
     * @return QueryInterface
     */
    public function whereNull(string $field, string $logic = 'AND'): QueryInterface
    {
        return $this->where($field, 'NULL', null, $logic);
    }

    /**
     * @param string $field
     * @param string $logic
     * @return QueryInterface
     */
    public function whereNotNull(string $field, string $logic = 'AND'): QueryInterface
    {
        return $this->where($field, 'NOT NULL', null, $logic);
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param string $logic
     * @return QueryInterface
     */
    public function whereIn(string $field, mixed $value, string $logic = 'AND'): QueryInterface
    {
        return $this->where($field, 'IN', $value, $logic);
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param string $logic
     * @return QueryInterface
     */
    public function whereNotIn(string $field, mixed $value, string $logic = 'AND'): QueryInterface
    {
        return $this->where($field, 'NOT IN', $value, $logic);
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param string $logic
     * @return QueryInterface
     */
    public function whereBetween(string $field, mixed $value, string $logic = 'AND'): QueryInterface
    {
        return $this->where($field, 'BETWEEN', $value, $logic);
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param string $logic
     * @return QueryInterface
     */
    public function whereNotBetween(string $field, mixed $value, string $logic = 'AND'): QueryInterface
    {
        return $this->where($field, 'NOT BETWEEN', $value, $logic);
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param string $logic
     * @return QueryInterface
     */
    public function whereLike(string $field, mixed $value, string $logic = 'AND'): QueryInterface
    {
        return $this->where($field, 'LIKE', $value, $logic);
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param string $logic
     * @return QueryInterface
     */
    public function whereNotLike(string $field, mixed $value, string $logic = 'AND'): QueryInterface
    {
        return $this->where($field, 'NOT LIKE', $value, $logic);
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param string $logic
     * @return QueryInterface
     */
    public function whereExists(string $field, mixed $value, string $logic = 'AND'): QueryInterface
    {
        return $this->where($field, 'EXISTS', $value, $logic);
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param string $logic
     * @return QueryInterface
     */
    public function whereNotExists(string $field, mixed $value, string $logic = 'AND'): QueryInterface
    {
        return $this->where($field, 'NOT EXISTS', $value, $logic);
    }

    /**
     * @param string $field
     * @param string $op
     * @param string|null $value
     * @param string $logic
     * @return QueryInterface
     */
    public function whereColumn(string $field, string $op, string $value = null, string $logic = 'AND'): QueryInterface
    {
        if ($value === null) {
            $value = $op;
            $op = '=';
        }
        return $this->where($field, 'COLUMN', [$op, $value], $logic);
    }

    /**
     * @param array $where
     * @return array
     */
    protected function parseWhereArray(array $where): array
    {
        $conditions = [];
        foreach ($where as $key => $value) {
            if (is_array($value)) {
                $conditions[] = $this->parseWhereOp(...$value);
            } else {
                $conditions[] = $this->parseWhereOp($key, $value);
            }
        }
        return $conditions;
    }

    /**
     * @param mixed $field
     * @param mixed|null $op
     * @param mixed|null $value
     * @return mixed
     */
    protected function parseWhereOp(string $field, mixed $op = null, mixed $value = null): mixed
    {
        if ($op === null) {
            $condition = $field;
        } else {
            $condition = [$field, $op, $value];
        }

        return $condition;
    }

    /**
     * @param Closure $closure
     * @return Closure
     */
    protected function parseWhereClosure(Closure $closure): Closure
    {
        $query = $this->newQuery();
        $result = $closure($query);
        if ($result instanceof QueryInterface) {
            $query = $result;
        }
        return function () use ($query) {
            return $query->getOptions()['where'];
        };
    }
}