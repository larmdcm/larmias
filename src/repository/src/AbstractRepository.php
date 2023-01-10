<?php

declare(strict_types=1);

namespace Larmias\Repository;

use Larmias\Repository\Concerns\HasEvent;
use Larmias\Repository\Contracts\RepositoryDriverInterface;
use Larmias\Repository\Contracts\QueryRelateInterface;
use Larmias\Repository\Foundation\Collection;

/**
 * @method self field(string|array $column = '*')
 * @method self fieldRaw(string $expression, array $bind = [])
 * @method self alias(string|array $alias)
 * @method self join(string|array $table, string $condition = null, string $type = 'INNER', array $bind = [])
 * @method self where(mixed $column, mixed $op = null, mixed $condition = null)
 * @method self whereOr(mixed $column, mixed $op = null, mixed $condition = null)
 * @method self whereRaw(string $expression, array $bind = [], string $logic = 'AND')
 * @method self whereIn(string $field, string|array $condition, string $logic = 'AND')
 * @method self whereNotIn(string $field, string|array $condition, string $logic = 'AND')
 * @method self whereBetween(string $field, string|array $condition, string $logic = 'AND')
 * @method self whereNotBetween(string $field, string|array $condition, string $logic = 'AND')
 * @method self whereLike(string $field, string|array $condition, string $logic = 'AND')
 * @method self whereNotLike(string $field, string|array $condition, string $logic = 'AND')
 * @method self whereNull(string $field, string $logic = 'AND')
 * @method self whereNotNull(string $field, string $logic = 'AND')
 * @method self whereExists(mixed $condition, string $logic = 'AND')
 * @method self whereNotExists(mixed $condition, string $logic = 'AND')
 * @method self when(bool|\Closure $condition, array|\Closure $query, array|\Closure $otherwise = null)
 * @method self orderBy(string|array $field, string $order = 'DESC')
 * @method self orderByRaw(string $expression, array $bind = [])
 * @method self groupBy(string|array $field)
 * @method self distinct(bool $distinct = true)
 * @method self union(mixed $query, bool $unionAll = false)
 * @method self having(string $having)
 * @method self limit(int $offset, int $length = null)
 * @method self callable(callable $callable)
 * @method self with(string|array $with)
 * @method self withJoin(string|array $with, string $joinType = 'INNER')
 * @method self lockForUpdate()
 * @method self sharedLock()
 * @method mixed find(int|string $id = null, string|array $column = '*', bool $throwIf = false)
 * @method mixed findWhere(mixed $condition = [], string|array $column = '*', bool $throwIf = false)
 * @method mixed findOrFail(int|string $id = null, string|array $column = '*')
 * @method mixed findWhereOrFail(mixed $condition = [], string|array $column = '*')
 * @method bool findExists(mixed $condition = [])
 * @method mixed findValue(mixed $condition = [], ?string $column = null, mixed $default = null)
 * @method Collection get(mixed $condition = [], string|array $column = '*')
 * @method Collection all(string|array $column = '*')
 * @method Collection columnWhere(mixed $condition = [], ?string $column = null, ?string $key = null)
 * @method Collection column(?string $column = null, ?string $key = null)
 * @method mixed paginate(mixed $condition = [], string|array $column = '*', array $options = [])
 * @method int updateByKey(array $data, string|array $value = [], ?string $column = null)
 * @method int deleteByKey(string|array $value = [], ?string $column = null)
 * @method int increment(mixed $condition, string $column, float $amount = 1, array $extra = [])
 * @method int decrement(mixed $condition, string $column, float $amount = 1, array $extra = [])
 * @method int findCount(mixed $condition = [], string $column = '*')
 * @method float findMax(mixed $condition, string $column)
 * @method float findMin(mixed $condition, string $column, float $amount = 1, array $extra = [])
 * @method float findAvg(mixed $condition, string $column, float $amount = 1, array $extra = [])
 * @method float findSum(mixed $condition, string $column, float $amount = 1, array $extra = [])
 * @method string getPrimaryKey()
 */
abstract class AbstractRepository
{
    use HasEvent;

    protected array $config = [];

    protected RepositoryConfig $reposConfig;

    protected ?RepositoryDriverInterface $driver = null;

    protected ?string $model = null;

    public function __construct()
    {
        $this->reposConfig = RepositoryConfig::make($this->config);
    }

    abstract public function model(): string;

    /**
     * 创建数据.
     *
     * @param array $data
     * @return mixed
     */
    public function create(array $data): mixed
    {
        if ($this->fireEvent('creating', $data) === false) {
            return false;
        }
        $model = $this->getDriver()->create($data);

        $this->fireEvent('created', $model);

        return $model;
    }

    /**
     * 更新数据.
     *
     * @param array $data
     * @param mixed $condition
     * @return int
     * @throws \Throwable
     */
    public function update(array $data, mixed $condition): int
    {
        if ($this->fireEvent('updating', $data, $condition) === false) {
            return 0;
        }
        $row = $this->getDriver()->update($data, $condition);

        $this->fireEvent('updated', $data, $condition, $row);

        return $row;
    }

    /**
     * 删除数据.
     *
     * @param mixed $condition
     * @return int
     * @throws \Throwable
     */
    public function delete(mixed $condition): int
    {
        if ($this->fireEvent('deleting', $condition) === false) {
            return 0;
        }
        $row = $this->getDriver()->delete($condition);

        $this->fireEvent('deleted', $condition, $row);

        return $row;
    }

    public function driver(?string $name = null): RepositoryDriverInterface
    {
        $config = $name ? RepositoryConfig::make($this->config, $name) : $this->reposConfig;
        return RepositoryFactory::driver($config, $this);
    }

    public function newQueryRelate(?string $name = null): QueryRelateInterface
    {
        $config = $name ? RepositoryConfig::make($this->config, $name) : $this->reposConfig;
        return RepositoryFactory::query($config);
    }

    public function newModel(): object
    {
        $model = $this->getModel();
        return new $model;
    }

    public function getModel(): string
    {
        if (!$this->model) {
            $this->setModel($this->model());
        }
        return $this->model;
    }

    public function setModel(string $model): self
    {
        $this->model = $model;
        return $this;
    }

    /**
     * @return RepositoryDriverInterface
     */
    public function getDriver(): RepositoryDriverInterface
    {
        if (!$this->driver) {
            $this->setDriver($this->driver());
        }
        return $this->driver;
    }

    /**
     * @param RepositoryDriverInterface $driver
     * @return $this
     */
    public function setDriver(RepositoryDriverInterface $driver): self
    {
        $this->driver = $driver;
        return $this;
    }

    /**
     * RepositoryAbstract __call.
     *
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        $result = $this->getDriver()->{$name}(...$arguments);
        if ($result instanceof RepositoryDriverInterface) {
            return $this->setDriver($result);
        }
        return $result;
    }
}