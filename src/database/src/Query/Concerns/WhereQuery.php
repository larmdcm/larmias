<?php

declare(strict_types=1);

namespace Larmias\Database\Query\Concerns;

use Larmias\Database\Entity\Expression;
use Larmias\Database\Query\BaseQuery;
use Larmias\Database\Contracts\ExpressionInterface;
use Closure;
use function Larmias\Support\is_empty;
use function strtoupper;
use function is_array;

/**
 * @mixin BaseQuery
 */
trait WhereQuery
{
    /**
     * WHERE查询
     * @param mixed $field
     * @param mixed|null $op
     * @param mixed|null $value
     * @param string $logic
     * @return static
     */
    public function where(mixed $field, mixed $op = null, mixed $value = null, string $logic = 'AND'): static
    {
        if (is_empty($field)) {
            return $this;
        }

        if (is_array($field)) {
            $conditions = $this->parseWhereArray($field);
        } else if ($field instanceof Closure) {
            $conditions = [$this->parseClosure($field)];
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
     * WHERE OR 查询
     * @param mixed $field
     * @param mixed|null $op
     * @param mixed|null $value
     * @return static
     */
    public function orWhere(mixed $field, mixed $op = null, mixed $value = null): static
    {
        return $this->where($field, $op, $value, 'OR');
    }

    /**
     * WHERE 原生条件查询
     * @param string $expression
     * @param array $bindings
     * @return static
     */
    public function whereRaw(string $expression, array $bindings = []): static
    {
        return $this->where(new Expression($expression, $bindings));
    }

    /**
     * @param string $field
     * @param string $logic
     * @return static
     */
    public function whereNull(string $field, string $logic = 'AND'): static
    {
        return $this->where($field, 'NULL', null, $logic);
    }

    /**
     * @param string $field
     * @param string $logic
     * @return static
     */
    public function whereNotNull(string $field, string $logic = 'AND'): static
    {
        return $this->where($field, 'NOT NULL', null, $logic);
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param string $logic
     * @return static
     */
    public function whereIn(string $field, mixed $value, string $logic = 'AND'): static
    {
        return $this->where($field, 'IN', $value, $logic);
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param string $logic
     * @return static
     */
    public function whereNotIn(string $field, mixed $value, string $logic = 'AND'): static
    {
        return $this->where($field, 'NOT IN', $value, $logic);
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param string $logic
     * @return static
     */
    public function whereBetween(string $field, mixed $value, string $logic = 'AND'): static
    {
        return $this->where($field, 'BETWEEN', $value, $logic);
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param string $logic
     * @return static
     */
    public function whereNotBetween(string $field, mixed $value, string $logic = 'AND'): static
    {
        return $this->where($field, 'NOT BETWEEN', $value, $logic);
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param string $logic
     * @return static
     */
    public function whereLike(string $field, mixed $value, string $logic = 'AND'): static
    {
        return $this->where($field, 'LIKE', $value, $logic);
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param string $logic
     * @return static
     */
    public function whereNotLike(string $field, mixed $value, string $logic = 'AND'): static
    {
        return $this->where($field, 'NOT LIKE', $value, $logic);
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param string $logic
     * @return static
     */
    public function whereExists(string $field, mixed $value, string $logic = 'AND'): static
    {
        return $this->where($field, 'EXISTS', $value, $logic);
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param string $logic
     * @return static
     */
    public function whereNotExists(string $field, mixed $value, string $logic = 'AND'): static
    {
        return $this->where($field, 'NOT EXISTS', $value, $logic);
    }

    /**
     * @param string $field
     * @param string|int $op
     * @param string|null $value
     * @param string $logic
     * @return static
     */
    public function whereColumn(string $field, string|int $op, string $value = null, string $logic = 'AND'): static
    {
        if ($value === null) {
            $value = $op;
            $op = '=';
        }
        return $this->where($field, 'COLUMN', [$op, $value], $logic);
    }

    /**
     * WHERE CLOSURE
     * @param Closure $closure
     * @return static
     */
    public function whereClosure(Closure $closure): static
    {
        return $this->where($closure);
    }

    /**
     * 按条件设置查询
     * @param mixed $condition
     * @param mixed $query
     * @param mixed|null $otherwise
     * @return static
     */
    public function when(mixed $condition, mixed $query, mixed $otherwise = null): static
    {
        if ($condition instanceof Closure) {
            $condition = $condition($this);
        }

        if ($condition) {
            if ($query instanceof Closure) {
                $query($this, $condition);
            } else {
                $this->where($query);
            }
        } elseif ($otherwise) {
            if ($otherwise instanceof Closure) {
                $otherwise($this, $condition);
            } else {
                $this->where($otherwise);
            }
        }

        return $this;
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
            if ($value instanceof Closure) {
                $value = $this->parseClosure($value);
            }
            $condition = [$field, $op, $value];
        }

        return $condition;
    }
}