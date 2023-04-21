<?php

declare(strict_types=1);

namespace Larmias\Database;

use Larmias\Database\Contracts\ExpressionInterface;
use Larmias\Database\Contracts\ManagerInterface;
use Larmias\Database\Contracts\QueryInterface;
use Larmias\Database\Contracts\TransactionInterface;
use Larmias\Database\Model\Collection;
use Larmias\Database\Model\Concerns\Attribute;
use Larmias\Database\Model\Concerns\Conversion;
use Larmias\Database\Model\Concerns\Timestamp;
use Larmias\Utils\Contracts\Arrayable;
use Larmias\Utils\Contracts\Jsonable;
use Closure;
use ArrayAccess;
use Stringable;
use JsonSerializable;
use function Larmias\Utils\class_basename;
use function method_exists;

/**
 * @method Model table(string $name)
 * @method string getTable()
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
 * @method TransactionInterface beginTransaction()
 * @method mixed transaction(\Closure $callback)
 */
abstract class Model implements ArrayAccess, Arrayable, Jsonable, Stringable, JsonSerializable
{
    use Attribute;
    use Conversion;
    use Timestamp;

    /**
     * 主键
     * @var string
     */
    protected string $primaryKey = 'id';

    /**
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
     * @param array $data
     * @return static
     */
    public static function new(array $data = []): static
    {
        return new static($data);
    }

    /**
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
     * @param array|string|Closure $id
     * @param bool $force
     * @return bool
     */
    public static function destroy(array|string|Closure $id, bool $force = false): bool
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

        if ($exists) {
            if (!isset($this->data[$primaryKey]) || $this->data[$primaryKey] === '') {
                $this->data[$primaryKey] = $id;
            }
            $this->setExists(true);
            if (empty($this->origin)) {
                $this->refreshOrigin();
            }
        }

        return $exists;
    }

    /**
     * @return string
     */
    public function generateUniqueId(): string
    {
        throw new \RuntimeException('Method not implemented.');
    }

    /**
     * 修改数据
     * @return bool
     */
    protected function updateData(): bool
    {
        $data = $this->getChangedData();

        return $this->query()->update($data, $this->getWhere()) > 0;
    }

    /**
     * @return array
     */
    protected function getWhere(): array
    {
        $where = [];
        $primaryKey = $this->getPrimaryKey();
        if (isset($this->data[$primaryKey])) {
            $where[$primaryKey] = $this->data[$primaryKey];
        }
        return $where;
    }

    /**
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
     * @return QueryInterface
     */
    public function query(): QueryInterface
    {
        if (!isset($this->query)) {
            $this->query = $this->newQuery();
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
     * @param string $name
     * @param array $args
     * @return mixed
     */
    public function __call(string $name, array $args): mixed
    {
        $query = $this->query();
        $result = $query->{$name}(...$args);
        if ($result instanceof QueryInterface) {
            return $this->setQuery($result);
        }
        return $this->toResult($result);
    }

    /**
     * @return bool
     */
    public function isExists(): bool
    {
        return $this->exists;
    }

    /**
     * @param bool $exists
     * @return self
     */
    public function setExists(bool $exists): self
    {
        $this->exists = $exists;
        return $this;
    }

    /**
     * @return string
     */
    public function getPrimaryKey(): string
    {
        return $this->primaryKey;
    }

    /**
     * @param string $primaryKey
     * @return self
     */
    public function setPrimaryKey(string $primaryKey): self
    {
        $this->primaryKey = $primaryKey;
        return $this;
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
        return $this->getAttribute($name, false) !== null;
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