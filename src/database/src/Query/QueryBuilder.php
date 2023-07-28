<?php

declare(strict_types=1);

namespace Larmias\Database\Query;

use Larmias\Contracts\CollectionInterface;
use Larmias\Contracts\PaginatorInterface;
use Larmias\Database\Contracts\BuilderInterface;
use Larmias\Database\Contracts\ConnectionInterface;
use Larmias\Database\Contracts\ExecuteResultInterface;
use Larmias\Database\Contracts\ExpressionInterface;
use Larmias\Database\Contracts\ModelInterface;
use Larmias\Database\Contracts\QueryInterface;
use Larmias\Database\Contracts\SqlPrepareInterface;
use Larmias\Database\Entity\Expression;
use Larmias\Database\Exceptions\ResourceNotFoundException;
use Larmias\Database\Query\Concerns\AggregateQuery;
use Larmias\Database\Query\Concerns\JoinQuery;
use Larmias\Database\Query\Concerns\ModelRelationQuery;
use Larmias\Database\Query\Concerns\Transaction;
use Larmias\Database\Query\Concerns\WhereQuery;
use Larmias\Paginator\Paginator;
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

class QueryBuilder implements QueryInterface
{
    use WhereQuery;
    use JoinQuery;
    use AggregateQuery;
    use Transaction;
    use ModelRelationQuery;

    /**
     * 查询选项
     * @var array
     */
    protected array $options = [
        'primary_key' => 'id',
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
        'incr' => [],
        'soft_delete' => [],
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
     * 获取表名称
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
     * 设置表名称不含前缀
     * @param string $name
     * @return QueryInterface
     */
    public function name(string $name): QueryInterface
    {
        return $this->table($this->connection->getConfig('prefix', '') . $name);
    }

    /**
     * 设置查询字段RAW
     * @param string $field
     * @param array $bindings
     * @return QueryInterface
     */
    public function fieldRaw(string $field, array $bindings = []): QueryInterface
    {
        $this->options['field'][] = new Expression($field, $bindings);
        return $this;
    }

    /**
     * 设置查询字段
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
     * 设置分组查询
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
     * 设置分组查询RAW
     * @param string $expression
     * @param array $bindings
     * @return QueryInterface
     */
    public function groupByRaw(string $expression, array $bindings = []): QueryInterface
    {
        $this->options['group'][] = new Expression($expression, $bindings);
        return $this;
    }

    /**
     * 设置排序查询
     * @param array|string $field
     * @param string $order
     * @return QueryInterface
     */
    public function orderBy(array|string $field, string $order = 'DESC'): QueryInterface
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
     * 设置排序查询RAW
     * @param string $expression
     * @param array $bindings
     * @return QueryInterface
     */
    public function orderByRaw(string $expression, array $bindings = []): QueryInterface
    {
        $this->options['order'][] = new Expression($expression, $bindings);
        return $this;
    }

    /**
     * @param string $expression
     * @param array $bindings
     * @return QueryInterface
     */
    public function having(string $expression, array $bindings = []): QueryInterface
    {
        $this->options['having']['AND'][] = new Expression($expression, $bindings);
        return $this;
    }

    /**
     * @param string $expression
     * @param array $bindings
     * @return QueryInterface
     */
    public function orHaving(string $expression, array $bindings = []): QueryInterface
    {
        $this->options['having']['OR'][] = new Expression($expression, $bindings);
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
     * 设置分页查询
     * @param int $page
     * @param int $listRows
     * @return QueryInterface
     */
    public function page(int $page, int $listRows = 25): QueryInterface
    {
        return $this->offset(($page - 1) * $listRows)->limit($listRows);
    }

    /**
     * 设置递增
     * @param string $field
     * @param float $step
     * @return QueryInterface
     */
    public function incr(string $field, float $step = 1.0): QueryInterface
    {
        $this->options['incr'][] = [$field, $step];
        return $this;
    }

    /**
     * 设置递减
     * @param string $field
     * @param float $step
     * @return QueryInterface
     */
    public function decr(string $field, float $step = 1.0): QueryInterface
    {
        return $this->incr($field, -$step);
    }

    /**
     * 设置软删除
     * @param string $field
     * @param array $condition
     * @return QueryInterface
     */
    public function useSoftDelete(string $field, array $condition): QueryInterface
    {
        $this->options['soft_delete'] = [$field, $condition];
        return $this;
    }

    /**
     * 构建SQL语句
     * @param int $buildType
     * @return string
     */
    public function buildSql(int $buildType = self::BUILD_SQL_SELECT): string
    {
        $sqlPrepare = match ($buildType) {
            self::BUILD_SQL_INSERT => $this->builder->insert($this->getOptions()),
            self::BUILD_SQL_BATCH_INSERT => $this->builder->insertAll($this->getOptions()),
            self::BUILD_SQL_UPDATE => $this->builder->update($this->getOptions()),
            self::BUILD_SQL_DELETE => $this->buildDelete(),
            default => $this->buildSelect()
        };
        return $this->connection->buildSql($sqlPrepare->getSql(), $sqlPrepare->getBindings());
    }

    /**
     * 执行查询
     * @param string $method
     * @param array|null $data
     * @param mixed $condition
     * @return ExecuteResultInterface
     */
    public function execute(string $method, ?array $data = null, mixed $condition = null): ExecuteResultInterface
    {
        if ($data !== null) {
            $this->data($data);
        }
        if ($condition !== null) {
            $this->where($condition);
        }
        if ($method === 'delete') {
            $sqlPrepare = $this->buildDelete();
        } else {
            $options = $this->getOptions();
            $sqlPrepare = $this->builder->{$method}($options);
        }
        return $this->connection->execute($sqlPrepare->getSql(), $sqlPrepare->getBindings());
    }

    /**
     * 插入数据
     * @param array|null $data
     * @return int
     */
    public function insert(?array $data = null): int
    {
        return $this->execute(__FUNCTION__, $data)->getRowCount();
    }

    /**
     * 插入数据返回新增ID
     * @param array|null $data
     * @return string
     */
    public function insertGetId(?array $data = null): string
    {
        return $this->execute('insert', $data)->getInsertId();
    }

    /**
     * 批量插入数据
     * @param array|null $data
     * @return int
     */
    public function insertAll(?array $data = null): int
    {
        return $this->execute(__FUNCTION__, $data)->getRowCount();
    }

    /**
     * 更新数据
     * @param array|null $data
     * @param mixed $condition
     * @return int
     */
    public function update(?array $data = null, mixed $condition = null): int
    {
        return $this->execute(__FUNCTION__, $data, $condition)->getRowCount();
    }

    /**
     * 删除数据
     * @param mixed $condition
     * @return int
     */
    public function delete(mixed $condition = null): int
    {
        return $this->execute(__FUNCTION__, condition: $condition)->getRowCount();
    }

    /**
     * 获取数据集合
     * @return CollectionInterface
     */
    public function get(): CollectionInterface
    {
        $sqlPrepare = $this->buildSelect();
        $items = $this->connection->query($sqlPrepare->getSql(), $sqlPrepare->getBindings())->getResultSet();
        if ($this->isToModelCollection()) {
            return $this->toModelCollection($items);
        }
        return Collection::make($items);
    }

    /**
     * 获取第一条数据
     * @return array|ModelInterface|null
     */
    public function first(): array|ModelInterface|null
    {
        return $this->limit(1)->get()->first();
    }

    /**
     * 获取第一条数据 查询失败抛出异常
     * @return array|ModelInterface|null
     */
    public function firstOrFail(): array|ModelInterface|null
    {
        $data = $this->first();
        if ($data === null) {
            throw new ResourceNotFoundException();
        }
        return $data;
    }

    /**
     * 根据主键查询数据
     * @param int|string $id
     * @return array|ModelInterface|null
     */
    public function find(int|string $id): array|ModelInterface|null
    {
        return $this->where($this->getPrimaryKey(), $id)->first();
    }

    /**
     * 根据主键查询数据 查询失败抛出异常
     * @param int|string $id
     * @return array|ModelInterface|null
     */
    public function findOrFail(int|string $id): array|ModelInterface|null
    {
        $data = $this->find($id);
        if ($data === null) {
            throw new ResourceNotFoundException();
        }
        return $data;
    }

    /**
     * 查询单列值
     * @param string $name
     * @param mixed|null $default
     * @return mixed
     */
    public function value(string $name, mixed $default = null): mixed
    {
        $data = $this->first();
        return $data ? $data[$name] ?? $default : $default;
    }

    /**
     * 查询单列值列表
     * @param string $value
     * @param string|null $key
     * @return CollectionInterface
     */
    public function pluck(string $value, ?string $key = null): CollectionInterface
    {
        return $this->get()->pluck($value, $key);
    }

    /**
     * 分页查询
     * @param array $config
     * @return PaginatorInterface
     */
    public function paginate(array $config = []): PaginatorInterface
    {
        $defaultConfig = [
            'query' => [], //url额外参数
            'fragment' => '', //url锚点
            'var_page' => 'page', //分页变量
            'page' => 1,// 页码
            'list_rows' => 25, //每页数量
            'total' => null, // 总页数
            'simple' => false, // 分页简单模式
        ];

        $config = array_merge($defaultConfig, $config);
        $page = max($config['page'], 1);
        $listRows = $config['list_rows'];
        $total = $config['total'];
        $results = new Collection();

        if (!$total && !$config['simple']) {
            $options = $this->getOptions();
            unset($this->options['order'], $this->options['limit'], $this->options['field']);
            $total = $this->count();
            if ($total > 0) {
                $results = $this->setOptions($options)->page($page, $listRows)->get();
            }
        } else {
            $results = $this->page($page, $listRows)->get();
        }

        return Paginator::make($results, $listRows, $page, $total, $config['simple'], $config);
    }

    /**
     * 数据分块查询
     * @param int $count
     * @param callable $callback
     * @param string $column
     * @param string $order
     * @return bool
     */
    public function chunk(int $count, callable $callback, string $column = 'id', string $order = 'asc'): bool
    {
        $order = strtolower($order);
        unset($this->options['order']);
        $resultSet = $this->orderBy($column, $order)->limit($count)->get();

        if (str_contains($column, '.')) {
            $key = explode('.', $column)[1];
        } else {
            $key = $column;
        }

        while ($resultSet->isNotEmpty()) {
            if (call_user_func($callback, $resultSet) === false) {
                return false;
            }
            $lastId = $resultSet->pop()[$key];
            $resultSet = $this->where($column, $order == 'asc' ? '>' : '<', $lastId)->get();
        }

        return true;
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
     * @param array $options
     * @return QueryInterface
     */
    public function setOptions(array $options): QueryInterface
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @return string
     */
    public function getPrimaryKey(): string
    {
        return $this->options['primary_key'];
    }

    /**
     * @param string $primaryKey
     * @return QueryInterface
     */
    public function setPrimaryKey(string $primaryKey): QueryInterface
    {
        $this->options['primary_key'] = $primaryKey;
        return $this;
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

    /**
     * @return SqlPrepareInterface
     */
    protected function buildSelect(): SqlPrepareInterface
    {
        if ($this->options['soft_delete']) {
            [$field, $condition] = $this->options['soft_delete'];
            $this->where([
                [$field, ...$condition]
            ]);
        }

        return $this->builder->select($this->getOptions());
    }

    /**
     * @return SqlPrepareInterface
     */
    protected function buildDelete(): SqlPrepareInterface
    {
        if ($this->options['soft_delete']) {
            [$field, $condition] = $this->options['soft_delete'];
            if ($condition) {
                return $this->buildSoftDelete($field, $condition);
            }
        }

        return $this->builder->delete($this->getOptions());
    }

    /**
     * @param string $field
     * @param array $condition
     * @return SqlPrepareInterface
     */
    protected function buildSoftDelete(string $field, array $condition): SqlPrepareInterface
    {
        if (count($condition) > 1) {
            $value = $condition[1];
        } else {
            $value = $condition[0];
            if ($value === null || (is_string($value) && in_array(strtoupper($value), ['NULL', 'IS NULL']))) {
                $value = null;
            }
        }
        $this->data([
            $field => $value,
        ]);
        return $this->builder->update($this->getOptions());
    }
}