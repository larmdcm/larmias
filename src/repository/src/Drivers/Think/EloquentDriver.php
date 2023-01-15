<?php

declare(strict_types=1);

namespace Larmias\Repository\Drivers\Think;

use Larmias\Repository\Contracts\QueryRelateInterface;
use Larmias\Repository\Drivers\Think\Concerns\HasData;
use Larmias\Repository\Drivers\Think\Concerns\SoftDelete;
use Larmias\Repository\Exceptions\ResourceNotFoundException;
use Larmias\Repository\Drivers\RepositoryDriver;
use Larmias\Repository\Exceptions\ResourceStoreException;
use Larmias\Repository\Foundation\Collection;
use Larmias\Repository\AbstractRepository;
use Larmias\Repository\Drivers\QueryRelate;
use think\db\exception\DbException;
use think\facade\Db;
use think\Model;
use think\Paginator;
use Throwable;
use Closure;
use think\db\Query;
use function Larmias\Utils\throw_if;

class EloquentDriver extends RepositoryDriver
{
    use HasData, SoftDelete;

    /**
     * @var array
     */
    protected array $data = [];

    /**
     * Eloquent __construct.
     *
     * @param AbstractRepository $repository
     */
    public function __construct(AbstractRepository $repository)
    {
        parent::__construct($repository);
        $this->resetQueryRelate();
    }

    /**
     * 根据主键查询单条数据
     *
     * @param int|string|null $id
     * @param string|array $column
     * @param bool $throwIf
     * @return Model
     * @throws Throwable
     */
    public function find(int|string $id = null, string|array $column = '*', bool $throwIf = false): mixed
    {
        $condition = \is_null($id) ? [] : [$this->getPrimaryKey() => $id];
        return $this->findWhere($condition, $column, $throwIf);
    }

    /**
     * 根据混合条件查询单条数据
     *
     * @param mixed $condition
     * @param string|array $column
     * @param bool $throwIf
     * @return Model
     * @throws Throwable
     */
    public function findWhere(mixed $condition = [], string|array $column = '*', bool $throwIf = false): mixed
    {
        $result = $this->onceQuery(function (QueryRelateInterface $query) use ($condition, $column) {
            return $query->field($column)->where($condition)->getQuery()->find();
        });
        $throwIf && throw_if(empty($result), ResourceNotFoundException::class);
        return $result;
    }

    /**
     * 根据主键查询单条数据 查询不到抛出异常
     *
     * @param int|string|null $id
     * @param string|array $column
     * @return Model
     * @throws Throwable
     */
    public function findOrFail(int|string $id = null, string|array $column = '*'): mixed
    {
        return $this->find($id, $column, true);
    }

    /**
     * 根据混合条件查询单条数据 查询不到抛出异常
     *
     * @param mixed $condition
     * @param string|array $column
     * @return Model
     * @throws Throwable
     */
    public function findWhereOrFail(mixed $condition = [], string|array $column = '*'): mixed
    {
        return $this->findWhere($condition, $column, true);
    }

    /**
     * 查询数据是否存在
     *
     * @param mixed $condition
     * @return bool
     * @throws Throwable
     */
    public function findExists(mixed $condition = []): bool
    {
        return $this->onceQuery(function (QueryRelateInterface $query) use ($condition) {
            return !empty($query->fieldRaw('1')->where($condition)->getQuery()->find());
        });
    }

    /**
     * 查询单值.
     *
     * @param mixed $condition
     * @param string|null $column
     * @param mixed $default
     * @return mixed
     * @throws Throwable
     */
    public function findValue(mixed $condition = [], ?string $column = null, mixed $default = null): mixed
    {
        $column = $column ?: $this->getPrimaryKey();
        $value = $this->onceQuery(fn(QueryRelateInterface $query) => $query->where($condition)->getQuery()->value($column));
        return empty($value) ? $default : $value;
    }

    /**
     * 查询全部数据
     *
     * @param string|array $column
     * @return Collection
     * @throws Throwable
     */
    public function all(string|array $column = '*'): Collection
    {
        return $this->get([], $column);
    }

    /**
     * 根据混合条件查询数据
     *
     * @param mixed $condition
     * @param array|string $column
     * @return Collection
     * @throws Throwable
     */
    public function get(mixed $condition = [], string|array $column = '*'): Collection
    {
        return $this->onceQuery(function (QueryRelateInterface $query) use ($condition, $column) {
            return new Collection($query->field($column)->where($condition)->getQuery()->select());
        });
    }

    /**
     * 单列条件查询.
     *
     * @param mixed $condition
     * @param string|null $column
     * @param string|null $key
     * @return Collection
     * @throws Throwable
     */
    public function columnWhere(mixed $condition = [], ?string $column = null, ?string $key = null): Collection
    {
        $column = $column ?: $this->getPrimaryKey();
        return $this->onceQuery(function (QueryRelateInterface $query) use ($condition, $column, $key) {
            return new Collection($query->where($condition)->getQuery()->column($column, $key ?: ''));
        });
    }

    /**
     * 单列查询.
     *
     * @param string|null $column
     * @param string|null $key
     * @return Collection
     * @throws Throwable
     */
    public function column(?string $column = null, ?string $key = null): Collection
    {
        return $this->columnWhere([], $column, $key);
    }

    /**
     * 分页查询.
     *
     * @param mixed $condition
     * @param string|array $column
     * @param array $options
     * @return Paginator
     * @throws Throwable
     * @throws DbException
     */
    public function paginate(mixed $condition = [], string|array $column = '*', array $options = []): mixed
    {
        return $this->onceQuery(function (QueryRelateInterface $query) use ($condition, $column, $options) {
            /** @var \think\db\Query */
            $thinkQuery = $query->field($column)->where($condition)->getQuery();
            return $thinkQuery->paginate($options, $options['simple'] ?? false);
        });
    }

    /**
     * 创建数据.
     *
     * @param array $data
     * @return Model
     * @throws ResourceStoreException
     */
    public function create(array $data): mixed
    {
        try {
            return $this->repository->newModel()->create($data);
        } catch (Throwable $e) {
            throw new ResourceStoreException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * 按条件更新数据.
     *
     * @param array $data
     * @param mixed $condition
     * @return int
     * @throws Throwable
     */
    public function update(array $data, mixed $condition = []): int
    {
        $data = $this->getUpdateData($data);
        return $this->onceQuery(function (QueryRelateInterface $query) use ($data, $condition) {
            return $query->where($condition)->getQuery()->update($data);
        });
    }

    /**
     * 按字段更新数据.
     *
     * @param array $data
     * @param string|array $value
     * @return int
     * @throws Throwable
     */
    public function updateByKey(array $data, string|array $value = [], ?string $column = null): int
    {
        $column = $column ?: $this->getPrimaryKey();
        $values = is_string($value) ? explode(',', $value) : $value;
        return $this->update($data, [$column => $values]);
    }

    /**
     * 按条件删除数据
     *
     * @param mixed $condition
     * @return int
     * @throws Throwable
     */
    public function delete(mixed $condition = []): int
    {
        return $this->onceQuery(function (QueryRelateInterface $query) use ($condition) {
            if ($this->isSoftDelete()) {
                return $this->softDelete($condition);
            }
            return $query->where($condition)->getQuery()->delete();
        });
    }

    /**
     * 按字段删除数据.
     *
     * @param string|array $value
     * @param string|null $column
     * @return int
     * @throws Throwable
     */
    public function deleteByKey(string|array $value = [], ?string $column = null): int
    {
        $column = $column ?: $this->getPrimaryKey();
        $values = is_string($value) ? explode(',', $value) : $value;
        return $this->delete([$column => $values]);
    }

    /**
     * 自增
     *
     * @param mixed $condition
     * @param string $column
     * @param float $amount
     * @param array $extra
     * @return int
     * @throws Throwable
     * @throws DbException
     */
    public function increment(mixed $condition, string $column, float $amount = 1, array $extra = []): int
    {
        return $this->onceQuery(function (QueryRelateInterface $query) use ($condition, $column, $amount, $extra) {
            /** @var Query */
            $thinkQuery = $query->where($condition)->getQuery();
            return $thinkQuery->inc($column, $amount)->update($extra);
        });
    }

    /**
     * 自减
     *
     * @param mixed $condition
     * @param string $column
     * @param float $amount
     * @param array $extra
     * @return int
     * @throws Throwable
     * @throws DbException
     */
    public function decrement(mixed $condition, string $column, float $amount = 1, array $extra = []): int
    {
        return $this->onceQuery(function (QueryRelateInterface $query) use ($condition, $column, $amount, $extra) {
            /** @var \think\db\Query */
            $thinkQuery = $query->where($condition)->getQuery();
            return $thinkQuery->dec($column, $amount)->update($extra);
        });
    }

    /**
     * 根据条件查询条数
     *
     * @param mixed $condition
     * @param string $column
     * @return int
     * @throws Throwable
     */
    public function findCount(mixed $condition = [], string $column = '*'): int
    {
        return $this->onceQuery(fn(QueryRelateInterface $query) => $query->where($condition)->getQuery()->count($column));
    }

    /**
     * 根据条件查询最大值
     *
     * @param mixed $condition
     * @param string $column
     * @return float
     * @throws Throwable
     */
    public function findMax(mixed $condition, string $column): float
    {
        return $this->onceQuery(fn(QueryRelateInterface $query) => $query->where($condition)->getQuery()->max($column));
    }

    /**
     * 根据条件查询最小值
     *
     * @param mixed $condition
     * @param string $column
     * @return float
     * @throws Throwable
     */
    public function findMin(mixed $condition, string $column): float
    {
        return $this->onceQuery(fn(QueryRelateInterface $query) => $query->where($condition)->getQuery()->min($column));
    }

    /**
     * 根据条件查询平均值
     *
     * @param mixed $condition
     * @param string $column
     * @return float
     * @throws Throwable
     */
    public function findAvg(mixed $condition, string $column): float
    {
        return $this->onceQuery(fn(QueryRelateInterface $query) => $query->where($condition)->getQuery()->avg($column));
    }

    /**
     * 根据条件查询总和
     *
     * @param mixed $condition
     * @param string $column
     * @return float
     * @throws Throwable
     */
    public function findSum($condition, string $column): float
    {
        return $this->onceQuery(fn(QueryRelateInterface $query) => $query->where($condition)->getQuery()->sum($column));
    }

    /**
     * 获取主键key
     *
     * @return string
     */
    public function getPrimaryKey(): string
    {
        return $this->getQueryRelate()->getQuery()->getPk();
    }

    /**
     * 开始事物
     *
     * @return void
     */
    public function beginTransaction(): void
    {
        Db::startTrans();
    }

    /**
     * 提交事物
     *
     * @return void
     */
    public function commit(): void
    {
        Db::commit();
    }

    /**
     * 事物回滚
     *
     * @return void
     */
    public function rollback(): void
    {
        Db::rollback();
    }

    /**
     * @param Closure $callback
     * @return mixed
     * @throws Throwable
     */
    public function onceQuery(Closure $callback): mixed
    {
        try {
            return $callback($this->getQueryRelate());
        } finally {
            $this->resetQueryRelate();
        }
    }

    /**
     * @return void
     */
    public function resetQueryRelate(): void
    {
        $this->setQueryRelate($this->newQueryRelate());
    }

    /**
     *
     * @return QueryRelate
     */
    public function newQueryRelate(): QueryRelate
    {
        return $this->repository->newQueryRelate()->setQuery($this->newQuery());
    }

    /**
     * @return Query
     * @throws Throwable
     */
    public function newQuery(): Query
    {
        /** @var Model $model */
        $model = $this->repository->newModel();
        return $model->db();
    }

    /**
     * Eloquent __call.
     *
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        $result = call_user_func_array([$this->getQueryRelate(), $name], $arguments);
        if ($result instanceof QueryRelateInterface) {
            return $this->setQueryRelate($result);
        }
        $this->resetQueryRelate();
        return $result;
    }
}