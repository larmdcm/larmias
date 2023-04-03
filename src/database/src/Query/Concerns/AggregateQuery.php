<?php

declare(strict_types=1);

namespace Larmias\Database\Query\Concerns;

use Larmias\Database\Query\Builder;

/**
 * @mixin Builder
 */
trait AggregateQuery
{
    /**
     * @param string $field
     * @return int
     */
    public function count(string $field = '*'): int
    {
        return (int)$this->aggregate(__FUNCTION__, $field);
    }

    /**
     * @param string $field
     * @return float
     */
    public function sum(string $field): float
    {
        return (float)$this->aggregate(__FUNCTION__, $field);
    }

    /**
     * @param string $field
     * @return float
     */
    public function min(string $field): float
    {
        return (float)$this->aggregate(__FUNCTION__, $field);
    }

    /**
     * @param string $field
     * @return float
     */
    public function max(string $field): float
    {
        return (float)$this->aggregate(__FUNCTION__, $field);
    }

    /**
     * @param string $field
     * @return float
     */
    public function avg(string $field): float
    {
        return (float)$this->aggregate(__FUNCTION__, $field);
    }

    /**
     * @param string $type
     * @param string $field
     * @return mixed
     */
    protected function aggregate(string $type, string $field): mixed
    {
        $name = 'larmias_' . $type;
        return $this->fieldRaw($this->builder->aggregate($type, $field, $name))->value($name);
    }
}