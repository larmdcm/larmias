<?php

declare(strict_types=1);

namespace Larmias\Database\Query;

use Larmias\Database\Exceptions\ResourceNotFoundException;
use Larmias\Database\Query\Contracts\QueryInterface;
use Larmias\Contracts\CollectionInterface;
use Larmias\Collection\Collection;
use function Larmias\Collection\data_get;
use function Larmias\Support\throw_if;

class Query extends BaseQuery implements QueryInterface
{
    /**
     * 获取数据集合
     * @return CollectionInterface
     */
    public function get(): CollectionInterface
    {
        return Collection::make($this->buildQuery()->getResultSet());
    }

    /**
     * 获取第一条数据
     * @return array|null
     */
    public function first(): ?array
    {
        return $this->limit(1)->get()->first();
    }

    /**
     * 获取第一条数据 查询失败抛出异常
     * @return array
     * @throws ResourceNotFoundException|\Throwable
     */
    public function firstOrFail(): array
    {
        $data = $this->first();
        throw_if($data === null, ResourceNotFoundException::class);
        return $data;
    }

    /**
     * 根据主键查询数据
     * @param int|string $id
     * @return array|null
     */
    public function find(int|string $id): ?array
    {
        return $this->where($this->getPrimaryKey(), $id)->first();
    }

    /**
     * 根据主键查询数据 查询失败抛出异常
     * @param int|string $id
     * @return array
     * @throws ResourceNotFoundException|\Throwable
     */
    public function findOrFail(int|string $id): array
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
}