<?php

declare(strict_types=1);

namespace Larmias\Database\Model;

use Closure;
use JsonSerializable;
use Larmias\Contracts\PaginatorInterface;
use Larmias\Database\Contracts\ExpressionInterface;
use Larmias\Database\Contracts\ManagerInterface;
use Larmias\Database\Model\Concerns\ModelEvent;
use Larmias\Database\Model\Contracts\ModelInterface;
use Larmias\Database\Model\Contracts\QueryInterface;
use Larmias\Database\Contracts\TransactionInterface;
use Larmias\Database\Model\Concerns\Attribute;
use Larmias\Database\Model\Concerns\Conversion;
use Larmias\Database\Model\Concerns\RelationShip;
use Larmias\Database\Model\Concerns\Timestamp;
use Larmias\Utils\Contracts\Arrayable;
use Larmias\Utils\Contracts\Jsonable;
use RuntimeException;
use Stringable;
use function Larmias\Utils\class_basename;
use function method_exists;
use function str_contains;
use function array_diff;

/**
 * @method static QueryInterface table(string|array $name)
 * @method static string getTable()
 * @method static string getName()
 * @method static QueryInterface alias(string|array $name)
 * @method static QueryInterface name(string $name)
 * @method static QueryInterface fieldRaw(string $field, array $bindings = [])
 * @method static QueryInterface field(string|array|ExpressionInterface $field)
 * @method static QueryInterface where(mixed $field, mixed $op = null, mixed $value = null, string $logic = 'AND')
 * @method static QueryInterface orWhere(mixed $field, mixed $op = null, mixed $value = null)
 * @method static QueryInterface whereRaw(string $expression, array $bindings = [])
 * @method static QueryInterface whereNull(string $field, string $logic = 'AND')
 * @method static QueryInterface whereNotNull(string $field, string $logic = 'AND')
 * @method static QueryInterface whereIn(string $field, mixed $value, string $logic = 'AND')
 * @method static QueryInterface whereNotIn(string $field, mixed $value, string $logic = 'AND')
 * @method static QueryInterface whereBetween(string $field, mixed $value, string $logic = 'AND')
 * @method static QueryInterface whereNotBetween(string $field, mixed $value, string $logic = 'AND')
 * @method static QueryInterface whereLike(string $field, mixed $value, string $logic = 'AND')
 * @method static QueryInterface whereNotLike(string $field, mixed $value, string $logic = 'AND')
 * @method static QueryInterface whereExists(string $field, mixed $value, string $logic = 'AND')
 * @method static QueryInterface whereNotExists(string $field, mixed $value, string $logic = 'AND')
 * @method static QueryInterface whereColumn(string $field, mixed $value, string $logic = 'AND')
 * @method static QueryInterface when(mixed $condition, mixed $query, mixed $otherwise = null)
 * @method static QueryInterface join(array|string $table, mixed $condition, string $joinType = 'INNER')
 * @method static QueryInterface innerJoin(array|string $table, mixed $condition)
 * @method static QueryInterface leftJoin(array|string $table, mixed $condition)
 * @method static QueryInterface rightJoin(array|string $table, mixed $condition)
 * @method static QueryInterface groupBy(array|string $field)
 * @method static QueryInterface groupByRaw(string $expression, array $bindings = [])
 * @method static QueryInterface orderBy(array|string $field, ?string $order = null)
 * @method static QueryInterface orderByRaw(string $expression, array $bindings = [])
 * @method static QueryInterface having(string $expression, array $bindings = [])
 * @method static QueryInterface orHaving(string $expression, array $bindings = [])
 * @method static QueryInterface offset(int $offset)
 * @method static QueryInterface limit(int $limit)
 * @method static QueryInterface page(int $page, int $listRows = 25)
 * @method static QueryInterface incr(string $field, float $step)
 * @method static QueryInterface decr(string $field, float $step)
 * @method static QueryInterface useSoftDelete(string $field, array $condition)
 * @method static QueryInterface lockForUpdate()
 * @method static QueryInterface sharedLock()
 * @method static QueryInterface union(mixed $union, bool $all = false)
 * @method static QueryInterface unionAll(mixed $union)
 * @method static QueryInterface distinct(bool $distinct = true)
 * @method static QueryInterface forceIndex(string $index)
 * @method static QueryInterface comment(string $comment)
 * @method static ExpressionInterface raw(string $sql, array $bindings = [])
 * @method static QueryInterface with(string|array $with)
 * @method static QueryInterface scope(string|array|Closure $scope, ...$args)
 * @method static int count(string $field = '*')
 * @method static float sum(string $field)
 * @method static float min(string $field)
 * @method static float max(string $field)
 * @method static float avg(string $field)
 * @method static string buildSql(int $buildType = QueryInterface::BUILD_SQL_SELECT)
 * @method static int insert(?array $data = null)
 * @method static string insertGetId(?array $data = null)
 * @method static int insertAll(?array $data = null)
 * @method static int update(?array $data = null, mixed $condition = null)
 * @method static Collection get()
 * @method static static first()
 * @method static static firstOrFail()
 * @method static static find(int|string $id)
 * @method static static findOrFail(int|string $id)
 * @method static mixed value(string $name, mixed $default = null)
 * @method static Collection pluck(string $value, ?string $key = null)
 * @method static PaginatorInterface paginate(array $config = [])
 * @method static bool chunk(int $count, callable $callback, string $column = 'id', string $order = 'asc')
 * @method static TransactionInterface beginTransaction()
 * @method static mixed transaction(\Closure $callback)
 */
abstract class Model implements ModelInterface, Arrayable, Jsonable, Stringable, JsonSerializable
{
    use Attribute;
    use Conversion;
    use Timestamp;
    use RelationShip;
    use ModelEvent;

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
     * 数据是否存在
     * @var bool
     */
    protected bool $exists = false;

    /**
     * 全局查询作用域
     * @var array
     */
    protected array $globalScope = [];

    /**
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        if ($this->name === null) {
            $this->name = class_basename(static::class);
        }

        $this->fill($data);

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
        $model = static::new($data);
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

        $query = static::query();

        if ($id instanceof Closure) {
            $query->where($id);
        } else {
            if (is_string($id)) {
                $id = str_contains($id, ',') ? explode(',', $id) : [$id];
            } else if (is_int($id)) {
                $id = [$id];
            }

            $query->whereIn($query->getPrimaryKey(), $id);
        }

        $resultSet = $query->get();

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

        return $this->whenFireEvent(['saving', 'saved'], function (Closure $before, Closure $after) {
            if (!$before()) {
                return false;
            }

            $result = $this->isExists() ? $this->updateData() : $this->insertData();
            if ($result) {
                $after();
                $this->refreshOrigin();
            }
            return $result;
        });
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

        $result = $this->newQuery()->delete($this->getWhere()) > 0;
        if ($result) {
            $this->exists(false);
        }

        return $result;
    }

    /**
     * 新增数据
     * @return bool
     */
    protected function insertData(): bool
    {
        return $this->whenFireEvent(['creating', 'created'], function (Closure $before, Closure $after) {

            if (!$before()) {
                return false;
            }

            $query = $this->newQuery();
            if ($this->incrementing) {
                $id = $query->data($this->data)->insertGetId();
                $exists = !empty($id);
            } else {
                $id = $this->generateUniqueId();
                $this->setAttribute($this->getPrimaryKey(), $id);
                $exists = $query->data($this->data)->insert() > 0;
            }

            $primaryKey = $this->getPrimaryKey();
            $this->exists($exists);

            if ($exists) {
                if (!isset($this->data[$primaryKey]) || $this->data[$primaryKey] === '') {
                    $this->data[$primaryKey] = $id;
                }
                $after();
            }

            return $exists;
        });
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
        return $this->whenFireEvent(['updating', 'updated'], function (Closure $before, Closure $after) {
            $data = $this->getChangedData();

            if (empty($data)) {
                return true;
            }

            if (!$before()) {
                return false;
            }

            $query = $this->newQuery();

            $result = $query->data($data)->update(condition: $this->getWhere()) > 0;

            if ($result) {
                $after();
            }

            return $result;
        });
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
     * 实例化查询
     * @return QueryInterface
     */
    public static function query(): QueryInterface
    {
        return static::new()->newQuery();
    }

    /**
     * 设置不使用的全局查询作用域
     * @param array|string|null $scope
     * @return QueryInterface
     */
    public static function withoutGlobalScope(array|string|null $scope = null): QueryInterface
    {
        return static::new()->newQuery($scope);
    }

    /**
     * 实例化查询
     * @param array|string|null $scope
     * @return QueryInterface
     */
    public function newQuery(array|string|null $scope = []): QueryInterface
    {
        $connection = $this->manager->connection($this->connection);
        $query = $this->manager->newModelQuery($connection);
        $query->name($this->name)->setPrimaryKey($this->getPrimaryKey())
            ->setModel($this);

        if ($this->table) {
            $query->table($this->table);
        }

        if ($scope !== null) {
            $globalScope = array_diff($this->globalScope, is_string($scope) ? explode(',', $scope) : $scope);
            $query->scope($globalScope);
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
     * @param array $events
     * @param Closure $handler
     * @return mixed
     */
    public function whenFireEvent(array $events, Closure $handler): mixed
    {
        return $handler(function () use ($events) {
            $this->fireEvent($events[0]);
        }, function () use ($events) {
            $this->fireEvent($events[1]);
        });
    }

    /**
     * Model __call.
     * @param string $name
     * @param array $args
     * @return mixed
     */
    public function __call(string $name, array $args)
    {
        return $this->newQuery()->{$name}(...$args);
    }

    /**
     * Model __callStatic.
     * @param string $name
     * @param array $args
     * @return mixed
     */
    public static function __callStatic(string $name, array $args)
    {
        return call_user_func_array([static::query(), $name], $args);
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
    public function exists(bool $exists): self
    {
        $this->exists = $exists;
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