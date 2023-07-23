<?php

declare(strict_types=1);

namespace Larmias\Database\Contracts;

use Larmias\Contracts\CollectionInterface;
use Larmias\Contracts\PaginatorInterface;

interface QueryInterface
{
    /**
     * @var int
     */
    public const BUILD_SQL_INSERT = 1;

    /**
     * @var int
     */
    public const BUILD_SQL_BATCH_INSERT = 2;

    /**
     * @var int
     */
    public const BUILD_SQL_DELETE = 3;

    /**
     * @var int
     */
    public const BUILD_SQL_UPDATE = 4;

    /**
     * @var int
     */
    public const BUILD_SQL_SELECT = 5;

    /**
     * 设置表名称
     * @param string $name
     * @return QueryInterface
     */
    public function table(string $name): QueryInterface;

    /**
     * @return string
     */
    public function getTable(): string;

    /**
     * 设置表别名
     * @param string|array $name
     * @return QueryInterface
     */
    public function alias(string|array $name): QueryInterface;

    /**
     * @param string $name
     * @return QueryInterface
     */
    public function name(string $name): QueryInterface;

    /**
     * @param string $field
     * @param array $bindings
     * @return QueryInterface
     */
    public function fieldRaw(string $field, array $bindings = []): QueryInterface;

    /**
     * @param string|array|ExpressionInterface $field
     * @return QueryInterface
     */
    public function field(string|array|ExpressionInterface $field): QueryInterface;

    /**
     * @param mixed $field
     * @param mixed|null $op
     * @param mixed|null $value
     * @param string $logic
     * @return QueryInterface
     */
    public function where(mixed $field, mixed $op = null, mixed $value = null, string $logic = 'AND'): QueryInterface;

    /**
     * @param mixed $field
     * @param mixed|null $op
     * @param mixed|null $value
     * @return QueryInterface
     */
    public function orWhere(mixed $field, mixed $op = null, mixed $value = null): QueryInterface;

    /**
     * @param string $expression
     * @param array $bindings
     * @return QueryInterface
     */
    public function whereRaw(string $expression, array $bindings = []): QueryInterface;

    /**
     * @param string $field
     * @param string $logic
     * @return QueryInterface
     */
    public function whereNull(string $field, string $logic = 'AND'): QueryInterface;

    /**
     * @param string $field
     * @param string $logic
     * @return QueryInterface
     */
    public function whereNotNull(string $field, string $logic = 'AND'): QueryInterface;

    /**
     * @param string $field
     * @param mixed $value
     * @param string $logic
     * @return QueryInterface
     */
    public function whereIn(string $field, mixed $value, string $logic = 'AND'): QueryInterface;

    /**
     * @param string $field
     * @param mixed $value
     * @param string $logic
     * @return QueryInterface
     */
    public function whereNotIn(string $field, mixed $value, string $logic = 'AND'): QueryInterface;

    /**
     * @param string $field
     * @param mixed $value
     * @param string $logic
     * @return QueryInterface
     */
    public function whereBetween(string $field, mixed $value, string $logic = 'AND'): QueryInterface;

    /**
     * @param string $field
     * @param mixed $value
     * @param string $logic
     * @return QueryInterface
     */
    public function whereNotBetween(string $field, mixed $value, string $logic = 'AND'): QueryInterface;

    /**
     * @param string $field
     * @param mixed $value
     * @param string $logic
     * @return QueryInterface
     */
    public function whereLike(string $field, mixed $value, string $logic = 'AND'): QueryInterface;

    /**
     * @param string $field
     * @param mixed $value
     * @param string $logic
     * @return QueryInterface
     */
    public function whereNotLike(string $field, mixed $value, string $logic = 'AND'): QueryInterface;

    /**
     * @param string $field
     * @param mixed $value
     * @param string $logic
     * @return QueryInterface
     */
    public function whereExists(string $field, mixed $value, string $logic = 'AND'): QueryInterface;

    /**
     * @param string $field
     * @param mixed $value
     * @param string $logic
     * @return QueryInterface
     */
    public function whereNotExists(string $field, mixed $value, string $logic = 'AND'): QueryInterface;

    /**
     * @param string $field
     * @param string $op
     * @param string|null $value
     * @param string $logic
     * @return QueryInterface
     */
    public function whereColumn(string $field, string $op, string $value = null, string $logic = 'AND'): QueryInterface;

    /**
     * @param array|string $table
     * @param mixed $condition
     * @param string $joinType
     * @return QueryInterface
     */
    public function join(array|string $table, mixed $condition, string $joinType = 'INNER'): QueryInterface;

    /**
     * @param array|string $table
     * @param mixed $condition
     * @return QueryInterface
     */
    public function innerJoin(array|string $table, mixed $condition): QueryInterface;

    /**
     * @param array|string $table
     * @param mixed $condition
     * @return QueryInterface
     */
    public function leftJoin(array|string $table, mixed $condition): QueryInterface;

    /**
     * @param array|string $table
     * @param mixed $condition
     * @return QueryInterface
     */
    public function rightJoin(array|string $table, mixed $condition): QueryInterface;

    /**
     * @param array|string $field
     * @return QueryInterface
     */
    public function groupBy(array|string $field): QueryInterface;

    /**
     * @param string $expression
     * @param array $bindings
     * @return QueryInterface
     */
    public function groupByRaw(string $expression, array $bindings = []): QueryInterface;

    /**
     * @param array|string $field
     * @param string $order
     * @return QueryInterface
     */
    public function orderBy(array|string $field, string $order = 'DESC'): QueryInterface;

    /**
     * @param string $expression
     * @param array $bindings
     * @return QueryInterface
     */
    public function orderByRaw(string $expression, array $bindings = []): QueryInterface;

    /**
     * @param string $expression
     * @param array $bindings
     * @return QueryInterface
     */
    public function having(string $expression, array $bindings = []): QueryInterface;

    /**
     * @param string $expression
     * @param array $bindings
     * @return QueryInterface
     */
    public function orHaving(string $expression, array $bindings = []): QueryInterface;

    /**
     * @param int $offset
     * @return QueryInterface
     */
    public function offset(int $offset): QueryInterface;

    /**
     * @param int $limit
     * @return QueryInterface
     */
    public function limit(int $limit): QueryInterface;

    /**
     * @param string $field
     * @param float $step
     * @return QueryInterface
     */
    public function incr(string $field, float $step = 1.0): QueryInterface;

    /**
     * @param string $field
     * @param float $step
     * @return QueryInterface
     */
    public function decr(string $field, float $step = 1.0): QueryInterface;

    /**
     * @param string $field
     * @param array $condition
     * @return QueryInterface
     */
    public function useSoftDelete(string $field, array $condition): QueryInterface;

    /**
     * @param string $field
     * @return int
     */
    public function count(string $field = '*'): int;

    /**
     * @param string $field
     * @return float
     */
    public function sum(string $field): float;

    /**
     * @param string $field
     * @return float
     */
    public function min(string $field): float;

    /**
     * @param string $field
     * @return float
     */
    public function max(string $field): float;

    /**
     * @param string $field
     * @return float
     */
    public function avg(string $field): float;

    /**
     * @param int $buildType
     * @return string
     */
    public function buildSql(int $buildType = self::BUILD_SQL_SELECT): string;

    /**
     * @param array|null $data
     * @return int
     */
    public function insert(?array $data = null): int;

    /**
     * @param array|null $data
     * @return string
     */
    public function insertGetId(?array $data = null): string;

    /**
     * @param array|null $data
     * @return int
     */
    public function insertAll(?array $data = null): int;

    /**
     * @param array|null $data
     * @param mixed $condition
     * @return int
     */
    public function update(?array $data = null, mixed $condition = null): int;

    /**
     * @param mixed $condition
     * @return int
     */
    public function delete(mixed $condition = null): int;

    /**
     * @return CollectionInterface
     */
    public function get(): CollectionInterface;

    /**
     * @return array|null
     */
    public function first(): ?array;

    /**
     * @return array|null
     */
    public function firstOrFail(): ?array;

    /**
     * @param int|string $id
     * @return array|null
     */
    public function find(int|string $id): ?array;

    /**
     * @param int|string $id
     * @return array|null
     */
    public function findOrFail(int|string $id): ?array;

    /**
     * @param string $name
     * @param mixed|null $default
     * @return mixed
     */
    public function value(string $name, mixed $default = null): mixed;

    /**
     * @param string $value
     * @param string|null $key
     * @return CollectionInterface
     */
    public function pluck(string $value, ?string $key = null): CollectionInterface;

    /**
     * 分页查询
     * @param array $config
     * @return PaginatorInterface
     */
    public function paginate(array $config): PaginatorInterface;

    /**
     * @return TransactionInterface
     */
    public function beginTransaction(): TransactionInterface;

    /**
     * @param \Closure $callback
     * @return mixed
     */
    public function transaction(\Closure $callback): mixed;

    /**
     * @return array
     */
    public function getOptions(): array;

    /**
     * @param array $options
     * @return QueryInterface
     */
    public function setOptions(array $options): QueryInterface;

    /**
     * @return string
     */
    public function getPrimaryKey(): string;

    /**
     * @param string $primaryKey
     * @return QueryInterface
     */
    public function setPrimaryKey(string $primaryKey): QueryInterface;

    /**
     * @return ConnectionInterface
     */
    public function getConnection(): ConnectionInterface;

    /**
     * @param ConnectionInterface $connection
     * @return QueryInterface
     */
    public function setConnection(ConnectionInterface $connection): QueryInterface;

    /**
     * @return BuilderInterface
     */
    public function getBuilder(): BuilderInterface;

    /**
     * @param BuilderInterface $builder
     * @return QueryInterface
     */
    public function setBuilder(BuilderInterface $builder): QueryInterface;
}