<?php

declare(strict_types=1);

namespace Larmias\Database;

use Larmias\Database\Contracts\ManagerInterface;
use Larmias\Database\Contracts\QueryInterface;
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
     * @var ManagerInterface
     */
    protected static ManagerInterface $manager;

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
        $this->data = $data;
        $this->origin = $data;

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
    public static function setManager(ManagerInterface $manager): void
    {
        static::$manager = $manager;
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

        return $this->isExists();
    }

    /**
     * 新增数据
     * @return bool
     */
    protected function insertData(): bool
    {
        $id = $this->query()->insertGetId($this->data);

        if (!empty($id)) {
            $this->data[$this->getPrimaryKey()] = $id;
            $this->setExists(true);
        }

        return $this->isExists();
    }

    /**
     * 修改数据
     * @return bool
     */
    protected function updateData(): bool
    {
        $data = $this->getChangeData();

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
     * @return QueryInterface
     */
    public function newQuery(): QueryInterface
    {
        $query = static::$manager->query(static::$manager->connection($this->connection));
        $query->name($this->name);
        if ($this->table) {
            $query->table($this->table);
        }
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
        return $result instanceof QueryInterface ? $this : $this->toResult($result);
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
        return $this->getAttribute($name) !== null;
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