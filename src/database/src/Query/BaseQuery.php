<?php

declare(strict_types=1);

namespace Larmias\Database\Query;

use Larmias\Contracts\CollectionInterface;
use Larmias\Contracts\PaginatorInterface;
use Larmias\Database\Contracts\BuilderInterface;
use Larmias\Database\Contracts\ConnectionInterface;
use Larmias\Database\Contracts\ExecuteResultInterface;
use Larmias\Database\Contracts\ExpressionInterface;
use Larmias\Database\Contracts\QueryInterface;
use Larmias\Database\Contracts\SqlPrepareInterface;
use Larmias\Database\Entity\Expression;
use Larmias\Database\Query\Concerns\AggregateQuery;
use Larmias\Database\Query\Concerns\Transaction;
use Larmias\Database\Query\Concerns\WhereQuery;
use Larmias\Database\Query\Concerns\JoinQuery;
use Larmias\Paginator\Paginator;
use Larmias\Collection\Arr;
use Larmias\Collection\Collection;
use Closure;
use RuntimeException;
use Larmias\Stringable\Str;
use function array_map;
use function array_merge;
use function array_unique;
use function explode;
use function implode;
use function is_array;
use function is_string;
use function preg_match;
use function call_user_func;
use function strtoupper;
use function count;
use const SORT_REGULAR;

abstract class BaseQuery implements QueryInterface
{
    use WhereQuery;
    use JoinQuery;
    use AggregateQuery;
    use Transaction;

    /**
     * 查询选项
     * @var array
     */
    protected array $options = [
        'primary_key' => 'id',
        'data' => [],
        'table' => '',
        'name' => '',
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
        'union' => [],
        'soft_delete' => [],
        'lock' => '',
        'distinct' => false,
        'forceIndex' => null,
        'comment' => null,
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
     * 设置数据
     * @param array $data
     * @return static
     */
    public function data(array $data): static
    {
        $this->options['data'] = $data;
        return $this;
    }

    /**
     * 获取数据
     * @return array
     */
    public function getData(): array
    {
        return $this->options['data'];
    }

    /**
     * 设置表名称
     * @param string|array $name
     * @return static
     */
    public function table(string|array $name): static
    {
        $table = [];

        if (is_string($name)) {
            if (str_ends_with($name, ')')) {
                $name = [$name];
            } else {
                $nameSplit = explode(',', $name);
                $name = [];
                foreach ($nameSplit as $nameItem) {
                    if (str_contains($nameItem, ' ')) {
                        [$item, $alias] = explode(' ', $nameItem);
                        $name[$item] = $alias;
                    } else {
                        $name[] = $nameItem;
                    }
                }
            }
        }

        foreach ($name as $key => $value) {
            if (is_numeric($key)) {
                $table[] = $value;
            } else {
                $table[$key] = $value;
                $this->alias([$key => $value]);
            }
        }

        if (Arr::isList($table) && count($table) == 1) {
            $table = $table[0];
        }

        $this->options['table'] = $table;

        return $this;
    }

    /**
     * 获取表名称
     * @return string|array
     */
    public function getTable(): string|array
    {
        return $this->options['table'];
    }

    /**
     * 获取名称不含表前缀
     * @return string
     */
    public function getName(): string
    {
        return $this->options['name'];
    }

    /**
     * 设置表别名
     * @param string|array $name
     * @return static
     */
    public function alias(string|array $name): static
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
     * @return static
     */
    public function name(string $name): static
    {
        $this->options['name'] = $name;
        return $this->table($this->connection->getConfig('prefix', '') . $name);
    }

    /**
     * 设置查询字段RAW
     * @param string $field
     * @param array $bindings
     * @return static
     */
    public function fieldRaw(string $field, array $bindings = []): static
    {
        $this->options['field'][] = new Expression($field, $bindings);
        return $this;
    }

    /**
     * 设置查询字段
     * @param string|array|ExpressionInterface $field
     * @return static
     */
    public function field(string|array|ExpressionInterface $field): static
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
     * @return static
     */
    public function groupBy(array|string $field): static
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
     * @return static
     */
    public function groupByRaw(string $expression, array $bindings = []): static
    {
        $this->options['group'][] = new Expression($expression, $bindings);
        return $this;
    }

    /**
     * 设置排序查询
     * @param array|string $field
     * @param string|null $order
     * @return static
     */
    public function orderBy(array|string $field, ?string $order = null): static
    {
        if (empty($order)) {
            $this->options['order'][] = $field;
        } else {
            $this->options['order'][] = is_string($field) ? [$field => $order] : [implode(',', $field) => $order];
        }
        return $this;
    }

    /**
     * 设置排序查询RAW
     * @param string $expression
     * @param array $bindings
     * @return static
     */
    public function orderByRaw(string $expression, array $bindings = []): static
    {
        $this->options['order'][] = new Expression($expression, $bindings);
        return $this;
    }

    /**
     * @param string $expression
     * @param array $bindings
     * @return static
     */
    public function having(string $expression, array $bindings = []): static
    {
        $this->options['having']['AND'][] = new Expression($expression, $bindings);
        return $this;
    }

    /**
     * @param string $expression
     * @param array $bindings
     * @return static
     */
    public function orHaving(string $expression, array $bindings = []): static
    {
        $this->options['having']['OR'][] = new Expression($expression, $bindings);
        return $this;
    }

    /**
     * @param int $offset
     * @return static
     */
    public function offset(int $offset): static
    {
        $this->options['offset'] = $offset;
        return $this;
    }

    /**
     * @param int $limit
     * @return static
     */
    public function limit(int $limit): static
    {
        $this->options['limit'] = $limit;
        return $this;
    }

    /**
     * 设置分页查询
     * @param int $page
     * @param int $perPage
     * @return static
     */
    public function page(int $page, int $perPage = 25): static
    {
        return $this->offset(($page - 1) * $perPage)->limit($perPage);
    }

    /**
     * 设置递增
     * @param string $field
     * @param float $step
     * @return static
     */
    public function incr(string $field, float $step = 1.0): static
    {
        $this->options['incr'][] = [$field, $step];
        return $this;
    }

    /**
     * 设置递减
     * @param string $field
     * @param float $step
     * @return static
     */
    public function decr(string $field, float $step = 1.0): static
    {
        return $this->incr($field, -$step);
    }

    /**
     * 设置软删除
     * @param string $field
     * @param array $condition
     * @return static
     */
    public function useSoftDelete(string $field, array $condition): static
    {
        $this->options['soft_delete'] = [$field, $condition];
        return $this;
    }

    /**
     * 设置悲观锁
     * @return static
     */
    public function lockForUpdate(): static
    {
        $this->options['lock'] = __FUNCTION__;
        return $this;
    }

    /**
     * 设置乐观锁
     * @return static
     */
    public function sharedLock(): static
    {
        $this->options['lock'] = __FUNCTION__;
        return $this;
    }

    /**
     * 设置UNION
     * @param mixed $union
     * @param bool $all
     * @return static
     */
    public function union(mixed $union, bool $all = false): static
    {
        $this->options['union']['type'] = $all ? 'UNION ALL' : 'UNION';

        if (!isset($this->options['union']['list'])) {
            $this->options['union']['list'] = [];
        }

        $union = Arr::wrap($union);
        foreach ($union as $key => $u) {
            if ($u instanceof Closure) {
                $union[$key] = $this->parseClosure($u);
            }
        }

        $this->options['union']['list'] = array_merge($this->options['union']['list'], $union);

        return $this;
    }

    /**
     * 设置UNION ALL
     * @param mixed $union
     * @return static
     */
    public function unionAll(mixed $union): static
    {
        return $this->union($union, true);
    }

    /**
     * 设置distinct查询
     * @param bool $distinct
     * @return static
     */
    public function distinct(bool $distinct = true): static
    {
        $this->options['distinct'] = $distinct;
        return $this;
    }

    /**
     * 设置强制索引
     * @param string $index
     * @return static
     */
    public function forceIndex(string $index): static
    {
        $this->options['forceIndex'] = $index;
        return $this;
    }

    /**
     * 查询注释
     * @param string $comment
     * @return static
     */
    public function comment(string $comment): static
    {
        $this->options['comment'] = $comment;
        return $this;
    }

    /**
     * 插入数据
     * @param array|null $data
     * @return int
     */
    public function insert(?array $data = null): int
    {
        return $this->buildExecute(__FUNCTION__, $data)->getRowCount();
    }

    /**
     * 插入数据返回新增ID
     * @param array|null $data
     * @return string|null
     */
    public function insertGetId(?array $data = null): ?string
    {
        return $this->buildExecute('insert', $data)->getInsertId();
    }

    /**
     * 批量插入数据
     * @param array|null $data
     * @return int
     */
    public function insertAll(?array $data = null): int
    {
        return $this->buildExecute(__FUNCTION__, $data)->getRowCount();
    }

    /**
     * 更新数据
     * @param array|null $data
     * @param mixed $condition
     * @return int
     */
    public function update(?array $data = null, mixed $condition = null): int
    {
        return $this->buildExecute(__FUNCTION__, $data, $condition)->getRowCount();
    }

    /**
     * 删除数据
     * @param mixed $condition
     * @return int
     */
    public function delete(mixed $condition = null): int
    {
        return $this->buildExecute(__FUNCTION__, condition: $condition)->getRowCount();
    }

    /**
     * 构建执行
     * @param string $method
     * @param array|null $data
     * @param mixed $condition
     * @return ExecuteResultInterface
     */
    protected function buildExecute(string $method, ?array $data = null, mixed $condition = null): ExecuteResultInterface
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
     * 构建查询
     * @return ExecuteResultInterface
     */
    protected function buildQuery(): ExecuteResultInterface
    {
        $sqlPrepare = $this->buildSelect();
        return $this->connection->query($sqlPrepare->getSql(), $sqlPrepare->getBindings());
    }

    /**
     * 执行增改查语句
     * @param string $sql
     * @param array $bindings
     * @return int
     */
    public function execute(string $sql, array $bindings = []): int
    {
        return $this->connection->execute($sql, $bindings)->getRowCount();
    }

    /**
     * 执行查询语句
     * @param string $sql
     * @param array $bindings
     * @return array
     */
    public function query(string $sql, array $bindings = []): array
    {
        return $this->connection->query($sql, $bindings)->getResultSet();
    }

    /**
     * 构建原生表达式
     * @param string $sql
     * @param array $bindings
     * @return ExpressionInterface
     */
    public function raw(string $sql, array $bindings = []): ExpressionInterface
    {
        return new Expression($sql, $bindings);
    }

    /**
     * 构建SQL语句
     * @param int $buildType
     * @param bool $sub
     * @return string
     */
    public function buildSql(int $buildType = self::BUILD_SQL_SELECT, bool $sub = false): string
    {
        $sqlPrepare = match ($buildType) {
            self::BUILD_SQL_INSERT => $this->builder->insert($this->getOptions()),
            self::BUILD_SQL_BATCH_INSERT => $this->builder->insertAll($this->getOptions()),
            self::BUILD_SQL_UPDATE => $this->builder->update($this->getOptions()),
            self::BUILD_SQL_DELETE => $this->buildDelete(),
            default => $this->buildSelect()
        };

        $sql = $this->connection->buildSql($sqlPrepare->getSql(), $sqlPrepare->getBindings());
        return $sub ? '( ' . $sql . ' )' : $sql;
    }

    /**
     * @return static
     */
    public function newQuery(): static
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
     * @return static
     */
    public function setOptions(array $options): static
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @param string $key
     * @return static
     */
    public function removeOptions(string $key): static
    {
        unset($this->options[$key]);
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
     * @return static
     */
    public function setPrimaryKey(string $primaryKey): static
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
     * @return static
     */
    public function setConnection(ConnectionInterface $connection): static
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
     * @return static
     */
    public function setBuilder(BuilderInterface $builder): static
    {
        $this->builder = $builder;
        return $this;
    }

    /**
     * 构建查询
     * @return SqlPrepareInterface
     */
    protected function buildSelect(): SqlPrepareInterface
    {
        if (!empty($this->options['soft_delete'])) {
            [$field, $condition] = $this->options['soft_delete'];
            $this->where([
                [$this->buildField($field), ...$condition]
            ]);
        }

        return $this->builder->select($this->getOptions());
    }

    /**
     * 构建删除
     * @return SqlPrepareInterface
     */
    protected function buildDelete(): SqlPrepareInterface
    {
        if (!empty($this->options['soft_delete'])) {
            [$field, $condition] = $this->options['soft_delete'];
            if ($condition) {
                return $this->buildSoftDelete($this->buildField($field), $condition);
            }
        }

        return $this->builder->delete($this->getOptions());
    }

    /**
     * 构建解析`字段`
     * @param string $field
     * @return string
     */
    protected function buildField(string $field): string
    {
        $table = $this->getTable();

        if (isset($this->options['alias'][$table])) {
            $table = $this->options['alias'][$table];
        }

        return Str::template($field, [
            'table' => $table,
        ], [
            'open' => '${', 'close' => '}',
        ]);
    }

    /**
     * 构建软删除
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

    /**
     * 实例化集合
     * @param mixed $items
     * @return CollectionInterface
     */
    protected function newCollection(mixed $items = []): CollectionInterface
    {
        return new Collection($items);
    }

    /**
     * 解析闭包
     * @param Closure $closure
     * @return Closure
     */
    protected function parseClosure(Closure $closure): Closure
    {
        return function () use ($closure): QueryInterface {
            $query = $this->newQuery();
            $result = $closure($query);
            if ($result instanceof QueryInterface) {
                $query = $result;
            }
            return $query;
        };
    }

    /**
     * 分页查询
     * @param int $perPage
     * @param string $pageName
     * @param int|null $page
     * @param array $config
     * @return PaginatorInterface
     */
    public function paginate(int $perPage = 25, string $pageName = 'page', ?int $page = null, array $config = []): PaginatorInterface
    {
        $defaultConfig = [
            'query' => [], //url额外参数
            'fragment' => '', //url锚点
            'page_name' => $pageName, //分页变量
            'page' => $page,// 页码
            'per_page' => $perPage, //每页数量
            'total' => null, // 总页数
            'simple' => false, // 分页简单模式
        ];

        $config = array_merge($defaultConfig, $config);

        if (!$config['page']) {
            $config['page'] = Paginator::getCurrentPage($config['page_name']);
        }

        $page = (int)max($config['page'], 1);
        $perPage = (int)$config['per_page'];
        $total = (int)$config['total'];
        $results = $this->newCollection();

        if (!$total && !$config['simple']) {
            $options = $this->getOptions();
            unset($this->options['order'], $this->options['limit'], $this->options['field']);
            $total = $this->count();
            if ($total > 0) {
                $results = $this->setOptions($options)->page($page, $perPage)->get();
            }
        } else {
            $results = $this->page($page, $perPage)->get();
        }

        return Paginator::make($results, $perPage, $page, $total, (bool)$config['simple'], $config);
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

        $options = $this->getOptions();

        while ($resultSet->isNotEmpty()) {
            if (call_user_func($callback, $resultSet) === false) {
                return false;
            }
            $lastId = $resultSet->pop()[$key];
            $resultSet = $this->setOptions($options)->where($column, $order == 'asc' ? '>' : '<', $lastId)->get();
        }

        return true;
    }

    /**
     * BaseQuery __call.
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function __call(string $method, array $args): mixed
    {
        if (strtolower(substr($method, 0, 6)) == 'findby') {
            // 根据某个字段获取记录
            $field = Str::snake(substr($method, 6));
            return $this->where($field, '=', $args[0])->first();
        } else if (strtolower(substr($method, 0, 11)) == 'findfieldby') {
            // 根据某个字段获取记录的某个值
            $name = Str::snake(substr($method, 11));
            return $this->where($name, '=', $args[0])->value($args[1]);
        } else if (strtolower(substr($method, 0, 7)) == 'orWhere') {
            // orWhere查询
            $name = Str::snake(substr($method, 7));
            array_unshift($args, $name);
            return call_user_func_array([$this, 'orWhere'], $args);
        } else if (strtolower(substr($method, 0, 5)) == 'where') {
            // where查询
            $name = Str::snake(substr($method, 5));
            array_unshift($args, $name);
            return call_user_func_array([$this, 'where'], $args);
        }

        throw new RuntimeException('method not exist:' . static::class . '->' . $method);
    }
}