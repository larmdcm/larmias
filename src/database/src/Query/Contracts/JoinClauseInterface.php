<?php

declare(strict_types=1);

namespace Larmias\Database\Query\Contracts;

interface JoinClauseInterface
{
    /**
     * JOIN ON
     * @param mixed $field
     * @param mixed|null $op
     * @param mixed|null $value
     * @param string $logic
     * @return static
     */
    public function on(mixed $field, mixed $op = null, mixed $value = null, string $logic = 'AND'): static;

    /**
     * JOIN OR ON
     * @param mixed $field
     * @param mixed|null $op
     * @param mixed|null $value
     * @return static
     */
    public function orOn(mixed $field, mixed $op = null, mixed $value = null): static;
}