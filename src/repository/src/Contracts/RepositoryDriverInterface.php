<?php

declare(strict_types=1);

namespace Larmias\Repository\Contracts;

use Larmias\Repository\Exceptions\ResourceStoreException;
use Larmias\Repository\Foundation\Collection;
use Throwable;

interface RepositoryDriverInterface
{
    /**
     * 根据主键查询单条数据
     *
     * @param int|string|null $id
     * @param string|array $column
     * @param bool $throwIf
     * @return mixed
     * @throws Throwable
     */
    public function find(int|string $id = null, string|array $column = '*', bool $throwIf = false): mixed;

    /**
     * 根据混合条件查询单条数据
     *
     * @param mixed $condition
     * @param string|array $column
     * @param bool $throwIf
     * @return mixed
     * @throws Throwable
     */
    public function findWhere(mixed $condition = [], string|array $column = '*', bool $throwIf = false): mixed;

    /**
     * 根据主键查询单条数据 查询不到抛出异常
     *
     * @param int|string|null $id
     * @param string|array $column
     * @return mixed
     * @throws Throwable
     */
    public function findOrFail(int|string $id = null, string|array $column = '*'): mixed;

    /**
     * 根据混合条件查询单条数据 查询不到抛出异常
     *
     * @param mixed $condition
     * @param string|array $column
     * @return mixed
     * @throws Throwable
     */
    public function findWhereOrFail(mixed $condition = [], string|array $column = '*'): mixed;

    /**
     * 查询数据是否存在
     *
     * @param mixed $condition
     * @return bool
     * @throws Throwable
     */
    public function findExists(mixed $condition = []): bool;

    /**
     * 查询单值.
     *
     * @param mixed $condition
     * @param string|null $column
     * @param mixed $default
     * @return mixed
     * @throws Throwable
     */
    public function findValue(mixed $condition = [], ?string $column = null, mixed $default = null): mixed;

    /**
     * 查询全部数据
     *
     * @param string|array $column
     * @return Collection
     * @throws Throwable
     */
    public function all(string|array $column = '*'): Collection;

    /**
     * 根据混合条件查询数据
     *
     * @param mixed $condition
     * @param array|string $column
     * @return Collection
     * @throws Throwable
     */
    public function get(mixed $condition = [], string|array $column = '*'): Collection;

    /**
     * 单列条件查询.
     *
     * @param mixed $condition
     * @param string|null $column
     * @param string|null $key
     * @return Collection
     * @throws Throwable
     */
    public function columnWhere(mixed $condition = [], ?string $column = null, ?string $key = null): Collection;

    /**
     * 单列查询.
     *
     * @param string|null $column
     * @param string|null $key
     * @return Collection
     * @throws Throwable
     */
    public function column(?string $column = null, ?string $key = null): Collection;

    /**
     * 分页查询.
     *
     * @param mixed $condition
     * @param string|array $column
     * @param array $options
     * @return mixed
     * @throws Throwable
     */
    public function paginate(mixed $condition = [], string|array $column = '*', array $options = []): mixed;

    /**
     * 创建数据.
     *
     * @param array $data
     * @return mixed
     * @throws ResourceStoreException
     */
    public function create(array $data): mixed;

    /**
     * 按条件更新数据.
     *
     * @param array $data
     * @param mixed $condition
     * @return int
     * @throws Throwable
     */
    public function update(array $data, mixed $condition = []): int;

    /**
     * 按字段更新数据.
     *
     * @param array $data
     * @param string|array $value
     * @return int
     * @throws Throwable
     */
    public function updateByKey(array $data, string|array $value = [], ?string $column = null): int;

    /**
     * 按条件删除数据
     *
     * @param mixed $condition
     * @return int
     * @throws Throwable
     */
    public function delete(mixed $condition = []): int;

    /**
     * 按字段删除数据.
     *
     * @param string|array $value
     * @param string|null $column
     * @return int
     * @throws Throwable
     */
    public function deleteByKey(string|array $value = [], ?string $column = null): int;

    /**
     * 自增
     *
     * @param mixed $condition
     * @param string $column
     * @param float $amount
     * @param array $extra
     * @return int
     * @throws Throwable
     */
    public function increment(mixed $condition, string $column, float $amount = 1, array $extra = []): int;

    /**
     * 自减
     *
     * @param mixed $condition
     * @param string $column
     * @param float $amount
     * @param array $extra
     * @return int
     * @throws Throwable
     */
    public function decrement(mixed $condition, string $column, float $amount = 1, array $extra = []): int;

    /**
     * 根据条件查询条数
     *
     * @param mixed $condition
     * @param string $column
     * @return int
     * @throws Throwable
     */
    public function findCount(mixed $condition = [], string $column = '*'): int;

    /**
     * 根据条件查询最大值
     *
     * @param mixed $condition
     * @param string $column
     * @return float
     */
    public function findMax(mixed $condition, string $column): float;

    /**
     * 根据条件查询最小值
     *
     * @param mixed $condition
     * @param string $column
     * @return float
     */
    public function findMin(mixed $condition, string $column): float;

    /**
     * 根据条件查询平均值
     *
     * @param mixed $condition
     * @param string $column
     * @return float
     */
    public function findAvg(mixed $condition, string $column): float;

    /**
     * 根据条件查询总和
     *
     * @param mixed $condition
     * @param string $column
     * @return float
     */
    public function findSum(mixed $condition, string $column): float;

    /**
     * 获取主键key
     *
     * @return string
     */
    public function getPrimaryKey(): string;
}