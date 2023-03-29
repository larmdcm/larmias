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
    protected function parseWhereOp(mixed $field, mixed $op = null, mixed $value = null): mixed
    {
        if ($op === null) {
            $condition = $field;
        } else {
            if ($value === null) {
                $value = $op;
                $op = '=';
            }
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