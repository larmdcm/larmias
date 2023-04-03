<?php

declare(strict_types=1);

namespace Larmias\Database;

use Larmias\Database\Contracts\ManagerInterface;
use Closure;
use Larmias\Database\Contracts\QueryInterface;
use function Larmias\Utils\class_basename;

abstract class Model
{
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
    protected ?string $connectName = null;

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
    public function __construct(protected array $data = [])
    {
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
    public static function newInstance(array $data = []): static
    {
        $model = new static($data);
        if (isset($data[$model->getPrimaryKey()])) {
            $model->setExists(true);
        }
        return $model;
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
        foreach ($data as $key => $value) {
            $this->data[$key] = $value;
        }

        return boolval($this->exists ? $this->updateData($this->data) : $this->insertData($this->data));
    }

    /**
     * 新增数据
     * @param array $data
     * @return string
     */
    protected function insertData(array $data): string
    {
        $id = $this->query()->insertGetId($data);

        if (!empty($id)) {
            $data[$this->primaryKey] = $id;
        }

        $this->data = $data;

        return $id;
    }

    /**
     * 修改数据
     * @param array $data
     * @param array $condition
     * @return bool
     */
    protected function updateData(array $data, array $condition = []): bool
    {
        $this->data = $data;
        if (isset($data[$this->primaryKey])) {
            $condition[$this->primaryKey] = $data[$this->primaryKey];
            unset($data[$this->primaryKey]);
        }
        $check = $this->query()->update($data, $condition) > 0;
        if ($check) {
            $this->data = $data;
        }
        return $check;
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
        $query = static::$manager->query(static::$manager->connection($this->connectName));
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
        return $result instanceof QueryInterface ? $this : $result;
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
}