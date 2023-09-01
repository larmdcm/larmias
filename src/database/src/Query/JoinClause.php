<?php

declare(strict_types=1);

namespace Larmias\Database\Query;

use Larmias\Database\Query\Contracts\JoinClauseInterface;
use Closure;

class JoinClause extends BaseQuery implements JoinClauseInterface
{
    /**
     * JOIN ON
     * @param mixed $field
     * @param mixed $op
     * @param mixed $value
     * @param string $logic
     * @return static
     */
    public function on(mixed $field, mixed $op = null, mixed $value = null, string $logic = 'AND'): static
    {
        if ($field instanceof Closure) {
            return $this->whereClosure($field);
        }

        return $this->whereColumn($field, $op, $value, $logic);
    }

    /**
     * JOIN OR ON
     * @param mixed $field
     * @param mixed $op
     * @param mixed $value
     * @return static
     */
    public function orOn(mixed $field, mixed $op = null, mixed $value = null): static
    {
        return $this->on($field, $op, $value, 'OR');
    }
}