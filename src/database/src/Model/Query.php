<?php

declare(strict_types=1);

namespace Larmias\Database\Model;

use Larmias\Database\Model\Concerns\ModelRelationQuery;
use Larmias\Database\Model\Contracts\CollectionInterface;
use Larmias\Database\Model\Contracts\QueryInterface;
use Larmias\Database\Query\BaseQuery;
use Larmias\Database\Exceptions\ResourceNotFoundException;
use function Larmias\Utils\throw_if;
use function Larmias\Utils\data_get;

class Query extends BaseQuery implements QueryInterface
{
    use ModelRelationQuery;

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
     * @throws ResourceNotFoundException|\Throwable
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
     * @throws ResourceNotFoundException|\Throwable
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
     * @param mixed $items
     * @return CollectionInterface
     */
    public function newCollection(mixed $items = []): CollectionInterface
    {
        return new Collection($items);
    }
}