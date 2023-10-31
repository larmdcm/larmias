<?php

declare(strict_types=1);

namespace Larmias\Database\Model;

use Larmias\Database\Model\Concerns\ModelRelationQuery;
use Larmias\Database\Model\Contracts\CollectionInterface;
use Larmias\Database\Model\Contracts\QueryInterface;
use Larmias\Database\Model\Contracts\ScopeInterface;
use Larmias\Database\Query\BaseQuery;
use Larmias\Database\Exceptions\ResourceNotFoundException;
use Larmias\Collection\Arr;
use Closure;
use Larmias\Stringable\Str;
use Throwable;
use function array_filter;
use function array_map;
use function str_contains;
use function Larmias\Support\throw_if;
use function Larmias\Collection\data_get;

class Query extends BaseQuery implements QueryInterface
{
    use ModelRelationQuery;

    /**
     * 插入数据
     * @param array|null $data
     * @return int
     */
    public function insert(?array $data = null): int
    {
        $this->setData($data);

        return parent::insert();
    }

    /**
     * 插入数据返回新增ID
     * @param array|null $data
     * @return string|null
     */
    public function insertGetId(?array $data = null): ?string
    {
        $this->setData($data);

        return parent::insertGetId();
    }

    /**
     * 插入数据返回新增ID
     * @param array|null $data
     * @return int
     */
    public function insertAll(?array $data = null): int
    {
        $this->setData($data);

        return parent::insertAll();
    }

    /**
     * 更新数据
     * @param array|null $data
     * @param mixed $condition
     * @return int
     */
    public function update(?array $data = null, mixed $condition = null): int
    {
        $this->setData($data, true);

        return parent::update(condition: $condition);
    }

    /**
     * 获取数据集合
     * @return CollectionInterface
     */
    public function get(): CollectionInterface
    {
        return $this->toModelCollection($this->buildQuery()->getResultSet());
    }

    /**
     * 获取第一条数据
     * @return Model|null
     */
    public function first(): ?Model
    {
        return $this->limit(1)->get()->first();
    }

    /**
     * 获取第一条数据 查询失败抛出异常
     * @return Model
     * @throws ResourceNotFoundException|Throwable
     */
    public function firstOrFail(): Model
    {
        $data = $this->first();
        throw_if($data === null, ResourceNotFoundException::class);
        return $data;
    }

    /**
     * 根据主键查询数据
     * @param int|string $id
     * @return Model|null
     */
    public function find(int|string $id): ?Model
    {
        return $this->where($this->getPrimaryKey(), $id)->first();
    }

    /**
     * 根据主键查询数据 查询失败抛出异常
     * @param int|string $id
     * @return Model
     * @throws ResourceNotFoundException|Throwable
     */
    public function findOrFail(int|string $id): Model
    {
        $data = $this->find($id);
        throw_if($data === null, ResourceNotFoundException::class);
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
        return data_get($data ?: [], $name, $default);
    }

    /**
     * 查询单列值列表
     * @param string $value
     * @param string|null $key
     * @return CollectionInterface
     */
    public function pluck(string $value, ?string $key = null): CollectionInterface
    {
        if ($key === null) {
            $this->field($value);
        }

        return $this->get()->pluck($value, $key);
    }

    /**
     * 设置写入数据
     * @param array|null $data
     * @param bool $isUpdate
     * @return void
     */
    protected function setData(?array $data = null, bool $isUpdate = false): void
    {
        if ($data) {
            $isTwoDimension = Arr::isTwoDimension($data);
            $data = $isTwoDimension ? array_map(fn($item) => $this->model::new($item)->getData(), $data) : $this->model::new($data)->getData();
            $this->data($data);
        }

        $data = $this->options['data'];

        if (!isset($isTwoDimension)) {
            $isTwoDimension = Arr::isTwoDimension($data);
        }

        $this->options['data'] = $isTwoDimension
            ? array_map(fn($item) => $this->handleData($item, $isUpdate), $data)
            : $this->handleData($data, $isUpdate);
    }

    /**
     * 处理写入数据
     * @param array $data
     * @param bool $isUpdate
     * @return array
     */
    protected function handleData(array $data, bool $isUpdate = false): array
    {
        $data = array_filter($data, fn($key) => $this->model->isFillable($key), ARRAY_FILTER_USE_KEY);

        $this->model->checkTimeStampWrite($data, $isUpdate);

        return $data;
    }

    /**
     * @param mixed $items
     * @return CollectionInterface
     */
    protected function newCollection(mixed $items = []): CollectionInterface
    {
        return new Collection($items);
    }

    /**
     * 设置查询作用域
     * @param array|string|Closure $scope
     * @param ...$args
     * @return static
     */
    public function scope(array|string|Closure $scope, ...$args): static
    {
        array_unshift($args, $this);

        if ($scope instanceof Closure) {
            call_user_func_array($scope, $args);
            return $this;
        }

        if (is_string($scope)) {
            $scope = explode(',', $scope);
        }

        foreach ($scope as $name) {
            $name = trim($name);

            if (str_contains($name, '\\')) {
                /** @var ScopeInterface $scopeInstance */
                $scopeInstance = new $name();
                $scopeInstance->apply(...$args);
                continue;
            }

            $method = 'scope' . Str::studly($name);

            if (method_exists($this->model, $method)) {
                call_user_func_array([$this->model, $method], $args);
            }
        }
        return $this;
    }

    /**
     * Query __call.
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function __call(string $method, array $args): mixed
    {
        if (method_exists($this->model, 'scope' . $method)) {
            // 动态调用命名范围
            $method = 'scope' . $method;
            array_unshift($args, $this);
            call_user_func_array([$this->model, $method], $args);
            return $this;
        }

        return parent::__call($method, $args);
    }
}