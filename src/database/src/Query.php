<?php

declare(strict_types=1);

namespace Larmias\Database;

use Larmias\Database\Contracts\ConnectionInterface;
use Larmias\Database\Contracts\ExpressionInterface;
use Larmias\Database\Contracts\BuilderInterface;
use Larmias\Database\Contracts\QueryInterface;
use Larmias\Database\Query\Expression;
use Larmias\Utils\Collection;
use function is_string;
use function is_array;
use function preg_match;
use function explode;
use function implode;
use function array_map;
use function array_merge;
use function array_unique;
use function strtoupper;
use const SORT_REGULAR;

class Query implements QueryInterface
{
    /**
     * @var array
     */
    protected array $options = [
        'table' => '',
        'alias' => [],
        'field' => [],
        'where' => [],
        'join' => [],
        'group' => [],
        'order' => [],
        'offset' => null,
        'limit' => null,
        'having' => '',
    ];

    /**
     * @var ConnectionInterface
     */
    protected ConnectionInterface $connection;

    /**
     * @var BuilderInterface
     */
    protected BuilderInterface $builder;

    /**
     * 设置表名称
     * @param string $name
     * @return QueryInterface
     */
    public function table(string $name): QueryInterface
    {
        $this->options['table'] = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getTable(): string
    {
        return $this->options['table'];
    }

    /**
     * 设置表别名
     * @param string|array $name
     * @return QueryInterface
     */
    public function alias(string|array $name): QueryInterface
    {
        if (is_string($name)) {
            $name = [$this->getTable() => $name];
        }
        $this->options['alias'] = array_merge($this->options['alias'], $name);
        return $this;
    }

    /**
     * @param string $name
     * @return QueryInterface
     */
    public function name(string $name): QueryInterface
    {
        return $this->table($this->connection->getConfig('prefix', '') . $name);
    }

    /**
     * @param string $field
     * @param array $binds
     * @return QueryInterface
     */
    public function fieldRaw(string $field, array $binds = []): QueryInterface
    {
        $this->options['field'][] = new Expression($field, $binds);
        return $this;
    }

    /**
     * @param string|array|ExpressionInterface $field
     * @return QueryInterface
     */
    public function field(string|array|ExpressionInterface $field): QueryInterface
    {
        if ($field instanceof ExpressionInterface) {
            $this->options['field'][] = $field;
            return $this;
        }

        if (is_string($field)) {
            if (preg_match('/[\<\'\"\(]/', $field)) {
                return $this->fieldRaw($field);
            }
            $field = array_map('trim', explode(',', $field));
        }

        if (is_array($this->options['field'])) {
            $field = array_merge($this->options['field'], $field);
        }

        $this->options['field'] = array_unique($field, SORT_REGULAR);
        return $this;
    }

    /**
     * @param mixed $field
     * @param mixed|null $op
     * @param mixed|null $value
     * @param string $logic
     * @return QueryInterface
     */
    public function where(mixed $field, mixed $op = null, mixed $value = null, string $logic = 'AND'): QueryInterface
    {
        if ($field instanceof ExpressionInterface) {
            $condition = $field;
        } else {
            if ($op === null) {
                $op = '=';
            } else if ($value === null) {
                $value = $op;
                $op = '=';
            }
            $condition = [$field, $op, $value];
        }

        $this->options['where'][strtoupper($logic)][] = $condition;
        return $this;
    }

    /**
     * @param array|string $table
     * @param mixed $condition
     * @param string $joinType
     * @return QueryInterface
     */
    public function join(array|string $table, mixed $condition, string $joinType = 'INNER'): QueryInterface
    {
        $this->options['join'][] = [$table, $condition, $joinType];
        return $this;
    }

    /**
     * @param array|string $field
     * @return QueryInterface
     */
    public function groupBy(array|string $field): QueryInterface
    {
        if (is_string($field)) {
            $field = explode(',', $field);
        }
        $this->options['group'][] = $field;
        return $this;
    }

    /**
     * @param string $expression
     * @param array $binds
     * @return QueryInterface
     */
    public function groupByRaw(string $expression, array $binds = []): QueryInterface
    {
        $this->options['group'][] = new Expression($expression, $binds);
        return $this;
    }

    /**
     * @param array|string $field
     * @param string $order
     * @return QueryInterface
     */
    public function orderBy(array|string $field, string $order = ''): QueryInterface
    {
        if (is_string($field)) {
            if (empty($order)) {
                $this->options['order'][] = $field;
            } else {
                $this->options['order'][] = [$field => $order];
            }
        } else {
            if (empty($order)) {
                $this->options['order'][] = $field;
            } else {
                $this->options['order'][] = [implode(',', $field) => $order];
            }
        }
        return $this;
    }

    /**
     * @param string $raw
     * @param array $binds
     * @return QueryInterface
     */
    public function orderByRaw(string $raw, array $binds = []): QueryInterface
    {
        $this->options['order'][] = new Expression($raw, $binds);
        return $this;
    }

    /**
     * @param string $having
     * @param array $binds
     * @return QueryInterface
     */
    public function having(string $having, array $binds = []): QueryInterface
    {
        $this->options['having']['AND'][] = new Expression($having, $binds);
        return $this;
    }

    /**
     * @param string $having
     * @param array $binds
     * @return QueryInterface
     */
    public function havingOr(string $having, array $binds = []): QueryInterface
    {
        $this->options['having']['OR'][] = new Expression($having, $binds);
        return $this;
    }

    /**
     * @param int $offset
     * @return QueryInterface
     */
    public function offset(int $offset): QueryInterface
    {
        $this->options['offset'] = $offset;
        return $this;
    }

    /**
     * @param int $limit
     * @return QueryInterface
     */
    public function limit(int $limit): QueryInterface
    {
        $this->options['limit'] = $limit;
        return $this;
    }

    /**
     * @param int $buildType
     * @return string
     */
    public function buildSql(int $buildType = self::BUILD_SQL_SELECT): string
    {
        $sqlPrepare = $this->builder->select($this);
        return $this->connection->buildSql($sqlPrepare->getSql(), $sqlPrepare->getBinds());
    }

    /**
     * @return Collection
     */
    public function get(): Collection
    {
        $sqlPrepare = $this->builder->select($this);
        $items = $this->connection->query($sqlPrepare->getSql(), $sqlPrepare->getBinds());
        return Collection::make($items);
    }

    /**
     * @return QueryInterface
     */
    public function newQuery(): QueryInterface
    {
        $query = new static();
        $query->setConnection($this->connection);
        $query->setBuilder($this->builder);
        return $query;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @return ConnectionInterface
     */
    public function getConnection(): ConnectionInterface
    {
        return $this->connection;
    }

    /**
     * @param ConnectionInterface $connection
     * @return QueryInterface
     */
    public function setConnection(ConnectionInterface $connection): QueryInterface
    {
        $this->connection = $connection;
        return $this;
    }

    /**
     * @return BuilderInterface
     */
    public function getBuilder(): BuilderInterface
    {
        return $this->builder;
    }

    /**
     * @param BuilderInterface $builder
     * @return QueryInterface
     */
    public function setBuilder(BuilderInterface $builder): QueryInterface
    {
        $this->builder = $builder;
        return $this;
    }
}