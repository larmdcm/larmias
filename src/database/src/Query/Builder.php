<?php

declare(strict_types=1);

namespace Larmias\Database\Query;

use Larmias\Contracts\CollectionInterface;
use Larmias\Database\Contracts\BuilderInterface;
use Larmias\Database\Contracts\ConnectionInterface;
use Larmias\Database\Contracts\ExecuteResultInterface;
use Larmias\Database\Contracts\ExpressionInterface;
use Larmias\Database\Contracts\QueryInterface;
use Larmias\Database\Entity\Expression;
use Larmias\Database\Query\Concerns\WhereQuery;
use Larmias\Utils\Collection;
use function array_map;
use function array_merge;
use function array_unique;
use function explode;
use function implode;
use function is_array;
use function is_string;
use function preg_match;
use const SORT_REGULAR;

class Builder implements QueryInterface
{
    use WhereQuery;

    /**
     * @var array
     */
    protected array $options = [
        'data' => [],
        'table' => '',
        'alias' => [],
        'field' => [],
        'where' => [],
        'join' => [],
        'group' => [],
        'order' => [],
        'offset' => null,
        'limit' => null,
        'having' => [],
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
     * @param array $data
     * @return QueryInterface
     */
    public function data(array $data): QueryInterface
    {
        $this->options['data'] = $data;
        return $this;
    }

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
    public function orHaving(string $having, array $binds = []): QueryInterface
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
        $sqlPrepare = $this->builder->select($this->getOptions());
        return $this->connection->buildSql($sqlPrepare->getSql(), $sqlPrepare->getBinds());
    }

    /**
     * @param string $method
     * @param array|null $data
     * @param mixed $condition
     * @return ExecuteResultInterface
     */
    public function executeResult(string $method, ?array $data = null, mixed $condition = null): ExecuteResultInterface
    {
        if ($data !== null) {
            $this->data($data);
        }
        if ($condition !== null) {
            $this->where($condition);
        }
        $options = $this->getOptions();
        $sqlPrepare = $this->builder->{$method}($options);
        return $this->connection->execute($sqlPrepare->getSql(), $sqlPrepare->getBinds());
    }

    /**
     * @param array|null $data
     * @return int
     */
    public function insert(?array $data = null): int
    {
        return $this->executeResult(__FUNCTION__, $data)->getRowCount();
    }

    /**
     * @param array|null $data
     * @return string
     */
    public function insertGetId(?array $data = null): string
    {
        return $this->executeResult('insert', $data)->getInsertId();
    }

    /**
     * @param array|null $data
     * @return int
     */
    public function insertAll(?array $data = null): int
    {
        return $this->executeResult(__FUNCTION__, $data)->getRowCount();
    }

    /**
     * @param array|null $data
     * @param mixed $condition
     * @return int
     */
    public function update(?array $data = null, mixed $condition = null): int
    {
        return $this->executeResult(__FUNCTION__, $data, $condition)->getRowCount();
    }

    /**
     * @param mixed $condition
     * @return int
     */
    public function delete(mixed $condition = null): int
    {
        return $this->executeResult(__FUNCTION__, condition: $condition)->getRowCount();
    }

    /**
     * @return CollectionInterface
     */
    public function get(): CollectionInterface
    {
        $sqlPrepare = $this->builder->select($this->getOptions());
        $items = $this->connection->query($sqlPrepare->getSql(), $sqlPrepare->getBinds())->getResultSet();
        return Collection::make($items);
    }

    /**
     * @return array|null
     */
    public function first(): ?array
    {
        if (!$this->options['limit']) {
            $this->limit(1);
        }
        return $this->get()->first();
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