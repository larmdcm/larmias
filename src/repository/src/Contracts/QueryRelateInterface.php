<?php

declare(strict_types=1);

namespace Larmias\Repository\Contracts;

use Closure;

interface QueryRelateInterface
{
    /**
     * 指定查询字段
     *
     * @param string|array $column
     * @return QueryRelateInterface
     */
    public function field(string|array $column = '*'): QueryRelateInterface;

    /**
     * 指定表达式方式查询字段
     *
     * @param string $expression
     * @param array $bind
     * @return QueryRelateInterface
     */
    public function fieldRaw(string $expression, array $bind = []): QueryRelateInterface;

    /**
     * 指定别名查询
     *
     * @param string|array $alias
     * @return QueryRelateInterface
     */
    public function alias(string|array $alias): QueryRelateInterface;

    /**
     * 指定连接查询
     *
     * @param string|array $table
     * @param string|null $condition
     * @param string $type
     * @param array $bind
     * @return QueryRelateInterface
     */
    public function join(string|array $table, string $condition = null, string $type = 'INNER', array $bind = []): QueryRelateInterface;

    /**
     * 指定and条件查询
     *
     * @param mixed $column
     * @param mixed $op
     * @param mixed $condition
     * @return QueryRelateInterface
     */
    public function where(mixed $column, mixed $op = null, mixed $condition = null): QueryRelateInterface;

    /**
     * 指定or条件查询
     *
     * @param mixed $column
     * @param mixed $op
     * @param mixed $condition
     * @return QueryRelateInterface
     */
    public function whereOr(mixed $column, mixed $op = null, mixed $condition = null): QueryRelateInterface;

    /**
     * 指定raw查询
     *
     * @param string $expression
     * @param array $bind
     * @param string $logic
     * @return QueryRelateInterface
     */
    public function whereRaw(string $expression, array $bind = [], string $logic = 'AND'): QueryRelateInterface;

    /**
     * 指定where in查询
     *
     * @param string $field
     * @param string|array $condition
     * @param string $logic
     * @return QueryRelateInterface
     */
    public function whereIn(string $field, string|array $condition, string $logic = 'AND'): QueryRelateInterface;

    /**
     * 指定where not in查询
     *
     * @param string $field
     * @param string|array $condition
     * @param string $logic
     * @return QueryRelateInterface
     */
    public function whereNotIn(string $field, string|array $condition, string $logic = 'AND'): QueryRelateInterface;

    /**
     * 指定where between查询
     *
     * @param string $field
     * @param string|array $condition
     * @param string $logic
     * @return QueryRelateInterface
     */
    public function whereBetween(string $field, string|array $condition, string $logic = 'AND'): QueryRelateInterface;

    /**
     * 指定where not between查询
     *
     * @param string $field
     * @param string|array $condition
     * @param string $logic
     * @return QueryRelateInterface
     */
    public function whereNotBetween(string $field, string|array $condition, string $logic = 'AND'): QueryRelateInterface;

    /**
     * 指定like查询
     *
     * @param string $field
     * @param string|array $condition
     * @param string $logic
     * @return QueryRelateInterface
     */
    public function whereLike(string $field, string|array $condition, string $logic = 'AND'): QueryRelateInterface;

    /**
     * 指定not like查询
     *
     * @param string $field
     * @param string|array $condition
     * @param string $logic
     * @return QueryRelateInterface
     */
    public function whereNotLike(string $field, string|array $condition, string $logic = 'AND'): QueryRelateInterface;

    /**
     * 指定null查询
     *
     * @param string $field
     * @param string $logic
     * @return QueryRelateInterface
     */
    public function whereNull(string $field, string $logic = 'AND'): QueryRelateInterface;

    /**
     * 指定not null查询
     *
     * @param string $field
     * @param string $logic
     * @return QueryRelateInterface
     */
    public function whereNotNull(string $field, string $logic = 'AND'): QueryRelateInterface;

    /**
     * 指定exists查询
     *
     * @param mixed $condition
     * @param string $logic
     * @return QueryRelateInterface
     */
    public function whereExists(mixed $condition, string $logic = 'AND'): QueryRelateInterface;

    /**
     * 指定not exists查询
     *
     * @param mixed $condition
     * @param string $logic
     * @return QueryRelateInterface
     */
    public function whereNotExists(mixed $condition, string $logic = 'AND'): QueryRelateInterface;

    /**
     * when 条件查询
     *
     * @param bool|Closure $condition
     * @param array|Closure $query
     * @param array|Closure|null $otherwise
     * @return QueryRelateInterface
     */
    public function when(bool|Closure $condition, array|Closure $query, array|Closure $otherwise = null): QueryRelateInterface;

    /**
     * 指定排序
     *
     * @param string|array $field
     * @param string $order
     * @return QueryRelateInterface
     */
    public function orderBy(string|array $field, string $order = 'DESC'): QueryRelateInterface;

    /**
     * 指定表达式方式排序
     *
     * @param string $field
     * @param array $bind
     * @return QueryRelateInterface
     */
    public function orderByRaw(string $expression, array $bind = []): QueryRelateInterface;

    /**
     * 指定分组查询
     *
     * @param string|array $field
     * @return QueryRelateInterface
     */
    public function groupBy(string|array $field): QueryRelateInterface;

    /**
     * 指定distinct查询
     *
     * @param bool $distinct
     * @return QueryRelateInterface
     */
    public function distinct(bool $distinct = true): QueryRelateInterface;

    /**
     * 指定union查询
     *
     * @param mixed $query
     * @param bool $unionAll
     * @return $this
     */
    public function union(mixed $query, bool $unionAll = false): QueryRelateInterface;

    /**
     * 指定having查询
     *
     * @param string $having
     * @return QueryRelateInterface
     */
    public function having(string $having): QueryRelateInterface;

    /**
     * 指定limit查询
     *
     * @param int $offset
     * @param int|null $length
     * @return QueryRelateInterface
     */
    public function limit(int $offset, int $length = null): QueryRelateInterface;

    /**
     * 指定callable查询
     *
     * @param callable $callable
     * @return $this
     */
    public function callable(callable $callable): QueryRelateInterface;

    /**
     * 指定with查询
     *
     * @param string|array $with
     * @return QueryRelateInterface
     */
    public function with(string|array $with): QueryRelateInterface;

    /**
     * 指定with join查询
     *
     * @param string|array $with
     * @param string $joinType
     * @return QueryRelateInterface
     */
    public function withJoin(string|array $with, string $joinType = 'INNER'): QueryRelateInterface;

    /**
     * 指定排它锁查询
     *
     * @return QueryRelateInterface
     */
    public function lockForUpdate(): QueryRelateInterface;

    /**
     * 指定共享锁查询
     *
     * @return QueryRelateInterface
     */
    public function sharedLock(): QueryRelateInterface;

    /**
     * @return object
     */
    public function getQuery(): object;

    /**
     * @param object $query
     * @return self
     */
    public function setQuery(object $query): QueryRelateInterface;
}