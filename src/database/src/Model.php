<?php

declare(strict_types=1);

namespace Larmias\Database;

use Closure;
use JsonSerializable;
use Larmias\Contracts\Arrayable;
use Larmias\Contracts\Jsonable;
use Larmias\Database\Contracts\ConnectionInterface;
use Larmias\Database\Contracts\ManagerInterface;
use Larmias\Database\Model\Concerns\Attribute;
use Larmias\Database\Model\Concerns\Conversion;
use Larmias\Database\Model\Concerns\ModelEvent;
use Larmias\Database\Model\Concerns\RelationShip;
use Larmias\Database\Model\Concerns\Timestamp;
use Larmias\Database\Model\Contracts\ModelInterface;
use Larmias\Database\Model\Contracts\QueryInterface;
use RuntimeException;
use Stringable;
use function array_diff;
use function Larmias\Support\class_basename;
use function method_exists;
use function str_contains;

/**
 * @mixin ModelIDE
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

        foreach (static::$maker as $maker) {
            $maker($this);
        }

        $this->fill($data);
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
     * @param bool $forceDelete
     * @return bool
     */
    public static function destroy(array|string|int|Closure $id, bool $forceDelete = false): bool
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
            if (method_exists($item, 'forceDelete')) {
                $item->forceDelete($forceDelete);
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
            $after();
            $this->refreshOrigin();
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

        return $this->whenFireEvent(['deleting', 'deleted'], function (Closure $before, Closure $after) {

            if (!$before()) {
                return false;
            }

            $result = $this->newQuery()->delete() > 0;
            $this->exists(false);
            $after();

            return $result;
        });
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

            if ($exists) {
                $primaryKey = $this->getPrimaryKey();
                if (!isset($this->data[$primaryKey]) || $this->data[$primaryKey] === '') {
                    $this->data[$primaryKey] = $id;
                }
                $after();
            }

            $this->exists($exists);

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

            $result = $query->data($data)->update() > 0;

            $after();

            return $result;
        });
    }

    /**
     * 获取更新条件
     * @return array
     */
    public function getWhere(): array
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
        $query = $this->manager->newModelQuery($this->getConnection());
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
     * @return ConnectionInterface
     */
    public function getConnection(): ConnectionInterface
    {
        return $this->manager->connection($this->connection);
    }

    /**
     * 设置查询条件
     * @param QueryInterface $query
     * @return void
     */
    protected function setQueryWhere(QueryInterface $query): void
    {
        if (property_exists($this, 'withTrashed') && !$this->withTrashed && method_exists($this, 'withNoTrashed')) {
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
            return $this->fireEvent($events[0]);
        }, function () use ($events) {
            return $this->fireEvent($events[1]);
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