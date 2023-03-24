<?php

declare(strict_types=1);

namespace Larmias\Database\Contracts;

interface QueryInterface
{
    /**
     * @var int
     */
    public const BUILD_SQL_INSERT = 1;

    /**
     * @var int
     */
    public const BUILD_SQL_DELETE = 2;

    /**
     * @var int
     */
    public const BUILD_SQL_UPDATE = 3;

    /**
     * @var int
     */
    public const BUILD_SQL_SELECT = 4;

    /**
     * @var int
     */
    public const BUILD_SQL_FIRST = 5;

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
     * @param int $buildType
     * @return string
     */
    public function buildSql(int $buildType = self::BUILD_SQL_SELECT): string;

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