<?php

declare(strict_types=1);

namespace Larmias\Database;

use Larmias\Contracts\PaginatorInterface;
use Larmias\Database\Contracts\ExpressionInterface;
use Larmias\Database\Contracts\ManagerInterface;
use Larmias\Database\Contracts\QueryInterface;
use Larmias\Database\Contracts\TransactionInterface;
use Larmias\Database\Model\Collection;
use Larmias\Database\Model\Concerns\Attribute;
use Larmias\Database\Model\Concerns\Conversion;
use Larmias\Database\Model\Concerns\ModelRelationQuery;
use Larmias\Database\Model\Concerns\RelationShip;
use Larmias\Database\Model\Concerns\Timestamp;
use Larmias\Utils\Contracts\Arrayable;
use Larmias\Utils\Contracts\Jsonable;
use Closure;
use ArrayAccess;
use Stringable;
use JsonSerializable;
use RuntimeException;
use function Larmias\Utils\class_basename;
use function method_exists;

/**
 * @method Model table(string $name)
 * @method string getTable()
 * @method string getName()
 * @method Model alias(string|array $name)
 * @method Model name(string $name)
 * @method Model fieldRaw(string $field, array $bindings = [])
 * @method Model field(string|array|ExpressionInterface $field)
 * @method Model where(mixed $field, mixed $op = null, mixed $value = null, string $logic = 'AND')
 * @method Model orWhere(mixed $field, mixed $op = null, mixed $value = null)
 * @method Model whereRaw(string $expression, array $bindings = [])
 * @method Model whereNull(string $field, string $logic = 'AND')
 * @method Model whereNotNull(string $field, string $logic = 'AND')
 * @method Model whereIn(string $field, mixed $value, string $logic = 'AND')
 * @method Model whereNotIn(string $field, mixed $value, string $logic = 'AND')
 * @method Model whereBetween(string $field, mixed $value, string $logic = 'AND')
 * @method Model whereNotBetween(string $field, mixed $value, string $logic = 'AND')
 * @method Model whereLike(string $field, mixed $value, string $logic = 'AND')
 * @method Model whereNotLike(string $field, mixed $value, string $logic = 'AND')
 * @method Model whereExists(string $field, mixed $value, string $logic = 'AND')
 * @method Model whereNotExists(string $field, mixed $value, string $logic = 'AND')
 * @method Model whereColumn(string $field, mixed $value, string $logic = 'AND')
 * @method Model join(array|string $table, mixed $condition, string $joinType = 'INNER')
 * @method Model innerJoin(array|string $table, mixed $condition)
 * @method Model leftJoin(array|string $table, mixed $condition)
 * @method Model rightJoin(array|string $table, mixed $condition)
 * @method Model groupBy(array|string $field)
 * @method Model groupByRaw(string $expression, array $bindings = [])
 * @method Model orderBy(array|string $field, string $order = 'DESC')
 * @method Model orderByRaw(string $expression, array $bindings = [])
 * @method Model having(string $expression, array $bindings = [])
 * @method Model orHaving(string $expression, array $bindings = [])
 * @method Model offset(int $offset)
 * @method Model limit(int $limit)
 * @method Model page(int $page, int $listRows = 25)
 * @method Model incr(string $field, float $step)
 * @method Model decr(string $field, float $step)
 * @method int count(string $field = '*')
 * @method float sum(string $field)
 * @method float min(string $field)
 * @method float max(string $field)
 * @method float avg(string $field)
 * @method string buildSql(int $buildType = QueryInterface::BUILD_SQL_SELECT)
 * @method int insert(?array $data = null)
 * @method string insertGetId(?array $data = null)
 * @method int insertAll(?array $data = null)
 * @method int update(?array $data = null, mixed $condition = null)
 * @method Collection get()
 * @method Model|null first()
 * @method Model firstOrFail()
 * @method Model|null find(int|string $id)
 * @method Model findOrFail(int|string $id)
 * @method mixed value(string $name, mixed $default = null)
 * @method Collection pluck(string $value, ?string $key = null)
 * @method PaginatorInterface paginate(array $config = [])
 * @method bool chunk(int $count, callable $callback, string $column = 'id', string $order = 'asc')
 * @method TransactionInterface beginTransaction()
 * @method mixed transaction(\Closure $callback)
 */
abstract class Model implements ArrayAccess, Arrayable, Jsonable, Stringable, JsonSerializable
{
    use Attribute;
    use Conversion;
    use Timestamp;
    use RelationShip;
    use ModelRelationQuery;

    /**
     * 主键
     * @var string
     */
    protected string $primaryKey = 'id';

    /**
     * 主键是否自增
     * @var bool
     */
    protected bool $incrementing = true;

    /**
     * @var ManagerInterface
     */
    protected ManagerInterface $manager;

    /**
     * @var Closure[]
     */
    protected static array $maker = [];

    /**
     * 数据表名称
     * @var string|null
     */
    protected ?string $name = null;

    /**
     * 数据表名
     * @var string|null
     */
    protected ?string $table = null;

    /**
     * 默认连接
     * @var string|null
     */
    protected ?string $connection = null;

    /**
     * @var QueryInterface
     */
    protected QueryInterface $query;

    /**
     * 数据是否存在
     * @var bool
     */
    protected bool $exists = false;

    /**
     * 当前是否处理Query
     * @var bool
     */
    protected bool $dealQuery = false;

    /**
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->setAttributes($data);

        $this->refreshOrigin();

        if ($this->name === null) {
            $this->name = class_basename(static::class);
        }

        foreach (static::$maker as $maker) {
            $maker($this);
        }

        if ($this->getPrimaryValue()) {
            $this->setExists(true);
        }
    }

    /**
     * @param Closure $maker
     * @return void
     */
    public static function maker(Closure $maker): void
    {
        static::$maker[] = $maker;
    }

    /**
     * @param ManagerInterface $manager
     */
    public function setManager(ManagerInterface $manager): void
    {
        $this->manager = $manager;
    }

    /**
     * 实例化模型
     * @param array $data
     * @return static
     */
    public static function new(array $data = []): static
    {
        return new static($data);
    }

    /**
     * 创建数据
     * @param array $data
     * @return static
     */
    public static function create(array $data): static
    {
        $model = new static($data);
        $model->save();
        return $model;
    }

    /**
     * 删除数据
     * @param array|string|int|Closure $id
     * @param bool $force
     * @return bool
     */
    public static function destroy(array|string|int|Closure $id, bool $force = false): bool
    {
        if (empty($id)) {
            return false;
        }

        $model = new static();

        if ($id instanceof Closure) {
            $model->query()->where($id);
        } else {
            if (is_string($id)) {
                $id = str_contains($id, ',') ? explode(',', $id) : [$id];
            } else if (is_int($id)) {
                $id = [$id];
            }

            $model->query()->whereIn($model->getPrimaryKey(), $id);
        }

        $resultSet = $model->get();

        /** @var Model $item */
        foreach ($resultSet as $item) {
            if (method_exists($item, 'force')) {
                $item->force($force);
            }
            $item->delete();
        }

        return true;
    }

    /**
     * 保存数据
     * @param array $data
     * @return bool
     */
    public function save(array $data = []): bool
    {
        $this->setAttributes($data);

        $this->checkTimeStampWrite();

        return $this->isExists() ? $this->updateData() : $this->insertData();
    }

    /**
     * 删除模型数据
     * @return bool
     */
    public function delete(): bool
    {
        if (!$this->isExists()) {
            return false;
        }

        $result = $this->query()->delete($this->getWhere()) > 0;
        if ($result) {
            $this->setExists(false);
        }

        return $result;
    }

    /**
     * 新增数据
     * @return bool
     */
    protected function insertData(): bool
    {
        if ($this->incrementing) {
            $id = $this->query()->insertGetId($this->data);
            $exists = !empty($id);
        } else {
            $id = $this->generateUniqueId();
            $exists = $this->query()->insert($this->data) > 0;
        }

        $primaryKey = $this->getPrimaryKey();
        $this->setExists($exists);

        if ($exists) {
            if (!isset($this->data[$primaryKey]) || $this->data[$primaryKey] === '') {
                $this->data[$primaryKey] = $id;
            }
            $this->refreshOrigin();
        }

        return $exists;
    }

    /**
     * 生成唯一id
     * @return string
     */
    public function generateUniqueId(): string
    {
        throw new RuntimeException('Method not implemented.');
    }

    /**
     * 修改数据
     * @return bool
     */
    protected function updateData(): bool
    {
        $data = $this->getChangedData();

        if (empty($data)) {
            return true;
        }

        $result = $this->query()->update($data, $this->getWhere()) > 0;

        if ($result) {
            $this->refreshOrigin($data);
        }

        return $result;
    }

    /**
     * 获取更新条件
     * @return array
     */
    protected function getWhere(): array
    {
        $where = [];
        $primaryKey = $this->getPrimaryKey();
        if (isset($this->origin[$primaryKey])) {
            $where[$primaryKey] = $this->origin[$primaryKey];
        }
        return $where;
    }

    /**
     * @return QueryInterface
     */
    public function query(): QueryInterface
    {
        if (!isset($this->query)) {
            $this->resetQuery();
        }

        return $this->query;
    }

    /**
     * @param QueryInterface $query
     * @return self
     */
    public function setQuery(QueryInterface $query): self
    {
        $this->query = $query;
        return $this;
    }

    /**
     * @return self
     */
    public function resetQuery(): self
    {
        $this->setQuery($this->newQuery());
        return $this;
    }

    /**
     * @return QueryInterface
     */
    public function newQuery(): QueryInterface
    {
        $query = $this->manager->newQuery($this->manager->connection($this->connection));
        $query->name($this->name)->setPrimaryKey($this->getPrimaryKey());
        if ($this->table) {
            $query->table($this->table);
        }

        $this->setQueryWhere($query);

        return $query;
    }

    /**
     * 设置查询条件
     * @param QueryInterface $query
     * @return void
     */
    protected function setQueryWhere(QueryInterface $query): void
    {
        if (isset($this->softDeleteField) && method_exists($this, 'withNoTrashed')) {
            $this->withNoTrashed($query);
        }
    }

    /**
     * @param string $name
     * @param array $args
     * @return mixed
     */
    public function __call(string $name, array $args)
    {
        $query = $this->query();
        $result = $query->{$name}(...$args);

        $this->dealQuery = $result instanceof QueryInterface;

        if ($this->dealQuery) {
            return $this->setQuery($result);
        }

        $result = $this->toResult($result);

        if ($this->isWithSet()) {
            $result = $this->withQuery($result);
        }

        return $result;
    }

    /**
     * @param string $name
     * @param array $args
     * @return mixed
     */
    public static function __callStatic(string $name, array $args)
    {
        return call_user_func_array([static::new(), $name], $args);
    }

    /**
     * 获取主键key
     * @return string
     */
    public function getPrimaryKey(): string
    {
        return $this->primaryKey;
    }

    /**
     * 设置主键key
     * @param string $primaryKey
     * @return self
     */
    public function setPrimaryKey(string $primaryKey): self
    {
        $this->primaryKey = $primaryKey;
        return $this;
    }

    /**
     * 获取当前模型数据是否存在
     * @return bool
     */
    public function isExists(): bool
    {
        return $this->exists;
    }

    /**
     * 设置当前模型数据是否存在
     * @param bool $exists
     * @return self
     */
    public function setExists(bool $exists): self
    {
        $this->exists = $exists;
        return $this;
    }

    /**
     * 是否处理query
     * @return bool
     */
    public function isDealQuery(): bool
    {
        return $this->dealQuery;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get(string $name): mixed
    {
        return $this->getAttribute($name);
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function __set(string $name, mixed $value): void
    {
        $this->setAttribute($name, $value);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return $this->hasAttribute($name);
    }

    /**
     * @param string $name
     * @return void
     */
    public function __unset(string $name): void
    {
        unset($this->data[$name]);
    }

    // ArrayAccess
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        $this->setAttribute($offset, $value);
    }

    #[\ReturnTypeWillChange]
    public function offsetExists($offset): bool
    {
        return $this->__isset($offset);
    }

    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        $this->__unset($offset);
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->getAttribute($offset);
    }
}