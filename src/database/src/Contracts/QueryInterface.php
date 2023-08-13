<?php

declare(strict_types=1);

namespace Larmias\Database\Contracts;

use Closure;
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
     * @param string|array $name
     * @return static
     */
    public function table(string|array $name): static;

    /**
     * 获取表名称
     * @return string
     */
    public function getTable(): string;

    /**
     * 设置表别名
     * @param string|array $name
     * @return static
     */
    public function alias(string|array $name): static;

    /**
     * 设置表名不含前缀
     * @param string $name
     * @return static
     */
    public function name(string $name): static;

    /**
     * @param string $field
     * @param array $bindings
     * @return static
     */
    public function fieldRaw(string $field, array $bindings = []): static;

    /**
     * @param string|array|ExpressionInterface $field
     * @return static
     */
    public function field(string|array|ExpressionInterface $field): static;

    /**
     * @param mixed $field
     * @param mixed|null $op
     * @param mixed|null $value
     * @param string $logic
     * @return static
     */
    public function where(mixed $field, mixed $op = null, mixed $value = null, string $logic = 'AND'): static;

    /**
     * @param mixed $field
     * @param mixed|null $op
     * @param mixed|null $value
     * @return static
     */
    public function orWhere(mixed $field, mixed $op = null, mixed $value = null): static;

    /**
     * @param string $expression
     * @param array $bindings
     * @return static
     */
    public function whereRaw(string $expression, array $bindings = []): static;

    /**
     * @param string $field
     * @param string $logic
     * @return static
     */
    public function whereNull(string $field, string $logic = 'AND'): static;

    /**
     * @param string $field
     * @param string $logic
     * @return static
     */
    public function whereNotNull(string $field, string $logic = 'AND'): static;

    /**
     * @param string $field
     * @param mixed $value
     * @param string $logic
     * @return static
     */
    public function whereIn(string $field, mixed $value, string $logic = 'AND'): static;

    /**
     * @param string $field
     * @param mixed $value
     * @param string $logic
     * @return static
     */
    public function whereNotIn(string $field, mixed $value, string $logic = 'AND'): static;

    /**
     * @param string $field
     * @param mixed $value
     * @param string $logic
     * @return static
     */
    public function whereBetween(string $field, mixed $value, string $logic = 'AND'): static;

    /**
     * @param string $field
     * @param mixed $value
     * @param string $logic
     * @return static
     */
    public function whereNotBetween(string $field, mixed $value, string $logic = 'AND'): static;

    /**
     * @param string $field
     * @param mixed $value
     * @param string $logic
     * @return static
     */
    public function whereLike(string $field, mixed $value, string $logic = 'AND'): static;

    /**
     * @param string $field
     * @param mixed $value
     * @param string $logic
     * @return static
     */
    public function whereNotLike(string $field, mixed $value, string $logic = 'AND'): static;

    /**
     * @param string $field
     * @param mixed $value
     * @param string $logic
     * @return static
     */
    public function whereExists(string $field, mixed $value, string $logic = 'AND'): static;

    /**
     * @param string $field
     * @param mixed $value
     * @param string $logic
     * @return static
     */
    public function whereNotExists(string $field, mixed $value, string $logic = 'AND'): static;

    /**
     * @param string $field
     * @param string $op
     * @param string|null $value
     * @param string $logic
     * @return static
     */
    public function whereColumn(string $field, string $op, string $value = null, string $logic = 'AND'): static;

    /**
     * @param array|string $table
     * @param mixed $condition
     * @param string $joinType
     * @return static
     */
    public function join(array|string $table, mixed $condition, string $joinType = 'INNER'): static;

    /**
     * @param array|string $table
     * @param mixed $condition
     * @return static
     */
    public function innerJoin(array|string $table, mixed $condition): static;

    /**
     * @param array|string $table
     * @param mixed $condition
     * @return static
     */
    public function leftJoin(array|string $table, mixed $condition): static;

    /**
     * @param array|string $table
     * @param mixed $condition
     * @return static
     */
    public function rightJoin(array|string $table, mixed $condition): static;

    /**
     * @param array|string $field
     * @return static
     */
    public function groupBy(array|string $field): static;

    /**
     * @param string $expression
     * @param array $bindings
     * @return static
     */
    public function groupByRaw(string $expression, array $bindings = []): static;

    /**
     * 设置排序查询
     * @param array|string $field
     * @param string|null $order
     * @return static
     */
    public function orderBy(array|string $field, ?string $order = null): static;

    /**
     * 设置原生排序查询
     * @param string $expression
     * @param array $bindings
     * @return static
     */
    public function orderByRaw(string $expression, array $bindings = []): static;

    /**
     * @param string $expression
     * @param array $bindings
     * @return static
     */
    public function having(string $expression, array $bindings = []): static;

    /**
     * @param string $expression
     * @param array $bindings
     * @return static
     */
    public function orHaving(string $expression, array $bindings = []): static;

    /**
     * @param int $offset
     * @return static
     */
    public function offset(int $offset): static;

    /**
     * @param int $limit
     * @return static
     */
    public function limit(int $limit): static;

    /**
     * 设置分页查询
     * @param int $page
     * @param int $perPage
     * @return static
     */
    public function page(int $page, int $perPage): static;

    /**
     * 设置递增
     * @param string $field
     * @param float $step
     * @return static
     */
    public function incr(string $field, float $step = 1.0): static;

    /**
     * 设置递减
     * @param string $field
     * @param float $step
     * @return static
     */
    public function decr(string $field, float $step = 1.0): static;

    /**
     * 设置软删除
     * @param string $field
     * @param array $condition
     * @return static
     */
    public function useSoftDelete(string $field, array $condition): static;

    /**
     * 设置悲观锁
     * @return static
     */
    public function lockForUpdate(): static;

    /**
     * 设置乐观锁
     * @return static
     */
    public function sharedLock(): static;

    /**
     * 设置UNION
     * @param mixed $union
     * @param bool $all
     * @return static
     */
    public function union(mixed $union, bool $all = false): static;

    /**
     * 设置UNION ALL
     * @param mixed $union
     * @return static
     */
    public function unionAll(mixed $union): static;

    /**
     * 指定distinct查询
     * @param bool $distinct
     * @return static
     */
    public function distinct(bool $distinct = true): static;

    /**
     * 指定强制索引
     * @param string $force
     * @return static
     */
    public function force(string $force): static;

    /**
     * 查询注释
     * @param string $comment
     * @return static
     */
    public function comment(string $comment): static;

    /**
     * 构建原生表达式
     * @param string $sql
     * @param array $bindings
     * @return ExpressionInterface
     */
    public function raw(string $sql, array $bindings = []): ExpressionInterface;

    /**
     * 执行增改查语句
     * @param string $sql
     * @param array $bindings
     * @return int
     */
    public function execute(string $sql, array $bindings = []): int;

    /**
     * 执行查询语句
     * @param string $sql
     * @param array $bindings
     * @return array
     */
    public function query(string $sql, array $bindings = []): array;

    /**
     * 插入数据返回影响条数
     * @param array|null $data
     * @return int
     */
    public function insert(?array $data = null): int;

    /**
     * 插入数据返回新增id
     * @param array|null $data
     * @return string|null
     */
    public function insertGetId(?array $data = null): ?string;

    /**
     * 插入数据集返回影响条数
     * @param array|null $data
     * @return int
     */
    public function insertAll(?array $data = null): int;

    /**
     * 根据条件更新数据
     * @param array|null $data
     * @param mixed $condition
     * @return int
     */
    public function update(?array $data = null, mixed $condition = null): int;

    /**
     * 根据条件删除数据
     * @param mixed $condition
     * @return int
     */
    public function delete(mixed $condition = null): int;

    /**
     * 分页查询
     * @param int $perPage
     * @param string $pageName
     * @param int|null $page
     * @param array $config
     * @return PaginatorInterface
     */
    public function paginate(int $perPage = 25, string $pageName = 'page', ?int $page = null, array $config = []): PaginatorInterface;

    /**
     * 数据分块查询
     * @param int $count
     * @param callable $callback
     * @param string $column
     * @param string $order
     * @return bool
     */
    public function chunk(int $count, callable $callback, string $column = 'id', string $order = 'acs'): bool;

    /**
     * 聚合查询条数
     * @param string $field
     * @return int
     */
    public function count(string $field = '*'): int;

    /**
     * 聚合查询求和
     * @param string $field
     * @return float
     */
    public function sum(string $field): float;

    /**
     * 聚合查询求最小值
     * @param string $field
     * @return float
     */
    public function min(string $field): float;

    /**
     * 聚合查询求最大值
     * @param string $field
     * @return float
     */
    public function max(string $field): float;

    /**
     * 聚合查询求平均值
     * @param string $field
     * @return float
     */
    public function avg(string $field): float;

    /**
     * 构建SQL
     * @param int $buildType
     * @param bool $sub
     * @return string
     */
    public function buildSql(int $buildType = self::BUILD_SQL_SELECT, bool $sub = false): string;

    /**
     * 开启事务
     * @return TransactionInterface
     */
    public function beginTransaction(): TransactionInterface;

    /**
     * 事务闭包
     * @param Closure $callback
     * @return mixed
     */
    public function transaction(Closure $callback): mixed;

    /**
     * @return array
     */
    public function getOptions(): array;

    /**
     * @param array $options
     * @return static
     */
    public function setOptions(array $options): static;

    /**
     * @return string
     */
    public function getPrimaryKey(): string;

    /**
     * @param string $primaryKey
     * @return static
     */
    public function setPrimaryKey(string $primaryKey): static;

    /**
     * @return ConnectionInterface
     */
    public function getConnection(): ConnectionInterface;

    /**
     * @param ConnectionInterface $connection
     * @return static
     */
    public function setConnection(ConnectionInterface $connection): static;

    /**
     * @return BuilderInterface
     */
    public function getBuilder(): BuilderInterface;

    /**
     * @param BuilderInterface $builder
     * @return static
     */
    public function setBuilder(BuilderInterface $builder): static;
}