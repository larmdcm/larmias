<?php

declare(strict_types=1);

namespace Larmias\Database\Contracts;

use Larmias\Contracts\CollectionInterface;

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
     * @var int
     */
    public const BUILD_SQL_FIRST = 6;

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
     * @param array $binds
     * @return QueryInterface
     */
    public function fieldRaw(string $field, array $binds = []): QueryInterface;

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
     * @param array|string $table
     * @param mixed $condition
     * @param string $joinType
     * @return QueryInterface
     */
    public function join(array|string $table, mixed $condition, string $joinType = 'INNER'): QueryInterface;

    /**
     * @param array|string $field
     * @return QueryInterface
     */
    public function groupBy(array|string $field): QueryInterface;

    /**
     * @param string $expression
     * @param array $binds
     * @return QueryInterface
     */
    public function groupByRaw(string $expression, array $binds = []): QueryInterface;

    /**
     * @param array|string $field
     * @param string $order
     * @return QueryInterface
     */
    public function orderBy(array|string $field, string $order = ''): QueryInterface;

    /**
     * @param string $raw
     * @param array $binds
     * @return QueryInterface
     */
    public function orderByRaw(string $raw, array $binds = []): QueryInterface;

    /**
     * @param string $having
     * @param array $binds
     * @return QueryInterface
     */
    public function having(string $having, array $binds = []): QueryInterface;

    /**
     * @param string $having
     * @param array $binds
     * @return QueryInterface
     */
    public function orHaving(string $having, array $binds = []): QueryInterface;

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
     * @return array
     */
    public function getOptions(): array;

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