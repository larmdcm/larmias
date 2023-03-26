<?php

declare(strict_types=1);

namespace Larmias\Database\Query\Concerns;

use Larmias\Database\Query\Builder;
use Larmias\Database\Contracts\QueryInterface;
use Larmias\Database\Contracts\ExpressionInterface;
use Closure;
use function strtoupper;

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
        if ($field instanceof Closure) {
            $condition = $this->parseWhereClosure($field);
        } else {
            $condition = $this->parseWhereOp($field, $op, $value);
        }

        $this->options['where'][strtoupper($logic)][] = $condition;

        return $this;
    }

    /**
     * @param mixed $field
     * @param mixed|null $op
     * @param mixed|null $value
     * @return mixed
     */
    protected function parseWhereOp(mixed $field, mixed $op = null, mixed $value = null): mixed
    {
        if ($field instanceof ExpressionInterface) {
            return $field;
        }

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