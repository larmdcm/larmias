<?php

declare(strict_types=1);

namespace Larmias\Database\Model\Contracts;

use Larmias\Database\Contracts\QueryInterface as BaseQueryInterface;
use Larmias\Database\Exceptions\ResourceNotFoundException;
use Larmias\Database\Model\Model;

interface QueryInterface extends BaseQueryInterface
{
    /**
     * 查询数据集合
     * @return CollectionInterface
     */
    public function get(): CollectionInterface;

    /**
     * 获取第一条数据
     * @return Model|null
     */
    public function first(): ?Model;

    /**
     * 获取第一条数据失败抛出异常
     * @return Model
     * @throws ResourceNotFoundException
     */
    public function firstOrFail(): Model;

    /**
     * 根据主键查询数据
     * @param int|string $id
     * @return Model|null
     */
    public function find(int|string $id): ?Model;

    /**
     * 根据主键查询数据 失败抛出异常
     * @param int|string $id
     * @return Model
     * @throws ResourceNotFoundException
     */
    public function findOrFail(int|string $id): Model;

    /**
     * 获取单列值
     * @param string $name
     * @param mixed|null $default
     * @return mixed
     */
    public function value(string $name, mixed $default = null): mixed;

    /**
     * 获取单列值集合
     * @param string $value
     * @param string|null $key
     * @return CollectionInterface
     */
    public function pluck(string $value, ?string $key = null): CollectionInterface;
}