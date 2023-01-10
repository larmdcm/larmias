<?php

declare(strict_types=1);

namespace Larmias\Repository\Drivers\Think;

use Larmias\Repository\Contracts\QueryRelateInterface;
use Larmias\Repository\Drivers\QueryRelate;
use think\db\Query;
use think\db\Raw;
use Closure;

/**
 * @property Query $query
 */
class EloquentQueryRelate extends QueryRelate
{
    /**
     * 指定查询字段
     *
     * @param string|array $column
     * @return QueryRelateInterface
     */
    public function field(string|array $column = '*'): QueryRelateInterface
    {
        $this->query->field($column);
        return $this;
    }

    /**
     * 指定表达式方式查询字段
     *
     * @param string $expression
     * @param array $bind
     * @return QueryRelateInterface
     */
    public function fieldRaw(string $expression, array $bind = []): QueryRelateInterface
    {
        $this->query->field(new Raw($expression, $bind));
        return $this;
    }

    /**
     * 指定别名查询
     *
     * @param string|array $alias
     * @return QueryRelateInterface
     */
    public function alias(string|array $alias): QueryRelateInterface
    {
        $this->query->alias($alias);
        return $this;
    }

    /**
     * 指定连接查询
     *
     * @param string|array $table
     * @param string|null $condition
     * @param string $type
     * @param array $bind
     * @return QueryRelateInterface
     */
    public function join(string|array $table, string $condition = null, string $type = 'INNER', array $bind = []): QueryRelateInterface
    {
        $this->query->join($table, $condition, $type, $bind);
        return $this;
    }

    /**
     * 指定and条件查询
     *
     * @param mixed $column
     * @param mixed $op
     * @param mixed $condition
     * @return QueryRelateInterface
     */
    public function where(mixed $column, mixed $op = null, mixed $condition = null): QueryRelateInterface
    {
        if (!empty($column)) {
            $this->query->where($column, $op, $condition);
        }
        return $this;
    }

    /**
     * 指定or条件查询
     *
     * @param mixed $column
     * @param mixed $op
     * @param mixed $condition
     * @return QueryRelateInterface
     */
    public function whereOr(mixed $column, mixed $op = null, mixed $condition = null): QueryRelateInterface
    {
        $this->query->whereOr($column, $op, $condition);
        return $this;
    }

    /**
     * 指定raw查询
     *
     * @param string $expression
     * @param array $bind
     * @param string $logic
     * @return QueryRelateInterface
     */
    public function whereRaw(string $expression, array $bind = [], string $logic = 'AND'): QueryRelateInterface
    {
        $this->query->whereRaw($expression, $bind, $logic);
        return $this;
    }

    /**
     * 指定where in查询
     *
     * @param string $field
     * @param string|array $condition
     * @param string $logic
     * @return QueryRelateInterface
     */
    public function whereIn(string $field, string|array $condition, string $logic = 'AND'): QueryRelateInterface
    {
        $this->query->whereIn($field, $condition, $logic);
        return $this;
    }

    /**
     * 指定where not in查询
     *
     * @param string $field
     * @param string|array $condition
     * @param string $logic
     * @return QueryRelateInterface
     */
    public function whereNotIn(string $field, string|array $condition, string $logic = 'AND'): QueryRelateInterface
    {
        $this->query->whereNotIn($field, $condition, $logic);
        return $this;
    }

    /**
     * 指定where between查询
     *
     * @param string $field
     * @param string|array $condition
     * @param string $logic
     * @return QueryRelateInterface
     */
    public function whereBetween(string $field, string|array $condition, string $logic = 'AND'): QueryRelateInterface
    {
        $this->query->whereBetween($field, $condition, $logic);
        return $this;
    }

    /**
     * 指定where not between查询
     *
     * @param string $field
     * @param string|array $condition
     * @param string $logic
     * @return QueryRelateInterface
     */
    public function whereNotBetween(string $field, string|array $condition, string $logic = 'AND'): QueryRelateInterface
    {
        $this->query->whereNotBetween($field, $condition, $logic);
        return $this;
    }

    /**
     * 指定like查询
     *
     * @param string $field
     * @param string|array $condition
     * @param string $logic
     * @return QueryRelateInterface
     */
    public function whereLike(string $field, string|array $condition, string $logic = 'AND'): QueryRelateInterface
    {
        $this->query->whereLike($field, $condition, $logic);
        return $this;
    }

    /**
     * 指定not like查询
     *
     * @param string $field
     * @param string|array $condition
     * @param string $logic
     * @return QueryRelateInterface
     */
    public function whereNotLike(string $field, string|array $condition, string $logic = 'AND'): QueryRelateInterface
    {
        $this->query->whereNotLike($field, $condition, $logic);
        return $this;
    }

    /**
     * 指定null查询
     *
     * @param string $field
     * @param string $logic
     * @return QueryRelateInterface
     */
    public function whereNull(string $field, string $logic = 'AND'): QueryRelateInterface
    {
        $this->query->whereNull($field, $logic);
        return $this;
    }

    /**
     * 指定not null查询
     *
     * @param string $field
     * @param string $logic
     * @return QueryRelateInterface
     */
    public function whereNotNull(string $field, string $logic = 'AND'): QueryRelateInterface
    {
        $this->query->whereNotNull($field);
        return $this;
    }

    /**
     * 指定exists查询
     *
     * @param mixed $condition
     * @param string $logic
     * @return QueryRelateInterface
     */
    public function whereExists(mixed $condition, string $logic = 'AND'): QueryRelateInterface
    {
        $this->query->whereExists($condition, $logic);
        return $this;
    }

    /**
     * 指定not exists查询
     *
     * @param mixed $condition
     * @param string $logic
     * @return QueryRelateInterface
     */
    public function whereNotExists(mixed $condition, string $logic = 'AND'): QueryRelateInterface
    {
        $this->query->whereNotExists($condition, $logic);
        return $this;
    }

    /**
     * when 条件查询
     *
     * @param bool|Closure $condition
     * @param array|Closure $query
     * @param array|Closure|null $otherwise
     * @return QueryRelateInterface
     */
    public function when(bool|Closure $condition, array|Closure $query, array|Closure $otherwise = null): QueryRelateInterface
    {
        $this->query->when($condition, $query, $otherwise);
        return $this;
    }

    /**
     * 指定排序
     *
     * @param string|array $field
     * @param string $order
     * @return QueryRelateInterface
     */
    public function orderBy(string|array $field, string $order = 'DESC'): QueryRelateInterface
    {
        $this->query->order($field, $order);
        return $this;
    }

    /**
     * 指定表达式方式排序
     *
     * @param string $expression
     * @param array $bind
     * @return QueryRelateInterface
     */
    public function orderByRaw(string $expression, array $bind = []): QueryRelateInterface
    {
        $this->query->orderRaw($expression, $bind);
        return $this;
    }

    /**
     * 指定分组查询
     *
     * @param string|array $field
     * @return QueryRelateInterface
     */
    public function groupBy(string|array $field): QueryRelateInterface
    {
        $this->query->group($field);
        return $this;
    }

    /**
     * 指定distinct查询
     *
     * @param bool $distinct
     * @return QueryRelateInterface
     */
    public function distinct(bool $distinct = true): QueryRelateInterface
    {
        $this->query->distinct($distinct);
        return $this;
    }

    /**
     * 指定union查询
     *
     * @param mixed $query
     * @param bool $unionAll
     * @return $this
     */
    public function union(mixed $query, bool $unionAll = false): QueryRelateInterface
    {
        $this->query->union($query, $unionAll);
        return $this;
    }

    /**
     * 指定having查询
     *
     * @param string $having
     * @return QueryRelateInterface
     */
    public function having(string $having): QueryRelateInterface
    {
        $this->query->having($having);
        return $this;
    }

    /**
     * 指定limit查询
     *
     * @param int $offset
     * @param int|null $length
     * @return QueryRelateInterface
     */
    public function limit(int $offset, int $length = null): QueryRelateInterface
    {
        $this->query->limit($offset, $length);
        return $this;
    }

    /**
     * 指定callable查询
     *
     * @param callable $callable
     * @return $this
     */
    public function callable(callable $callable): QueryRelateInterface
    {
        $result = \call_user_func($callable, $this->query);
        if ($result instanceof Query) {
            $this->query = $query;
        }
        return $this;
    }

    /**
     * 指定with查询
     *
     * @param string|array $with
     * @return QueryRelateInterface
     */
    public function with(string|array $with): QueryRelateInterface
    {
        $this->query->with($with);
        return $this;
    }

    /**
     * 指定with join查询
     *
     * @param string|array $with
     * @param string $joinType
     * @return QueryRelateInterface
     */
    public function withJoin(string|array $with, string $joinType = 'INNER'): QueryRelateInterface
    {
        $this->query->withJoin($with, $joinType);
        return $this;
    }

    /**
     * 指定排它锁查询
     *
     * @return QueryRelateInterface
     */
    public function lockForUpdate(): QueryRelateInterface
    {
        $this->query->lock(true);
        return $this;
    }

    /**
     * 指定共享锁查询
     *
     * @return QueryRelateInterface
     */
    public function sharedLock(): QueryRelateInterface
    {
        $this->query->lock('lock in share mode');
        return $this;
    }
}