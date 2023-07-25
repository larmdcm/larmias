<?php

declare(strict_types=1);

namespace Larmias\Database\Model\Relation;

use Larmias\Contracts\CollectionInterface;
use Larmias\Database\Model;
use Larmias\Utils\Arr;
use Larmias\Database\Model\Collection;
use Larmias\Database\Model\Pivot;
use RuntimeException;
use Closure;
use function is_array;
use function is_numeric;
use function is_string;

class BelongsToMany extends Relation
{
    /**
     * @param Model $parent
     * @param string $modelClass
     * @param string $middleClass
     * @param string $foreignKey
     * @param string $localKey
     */
    public function __construct(
        protected Model  $parent, protected string $modelClass, protected string $middleClass,
        protected string $foreignKey, protected string $localKey
    )
    {
        $this->model = $this->newModel();
    }

    /**
     * 初始化模型查询
     * @return void
     */
    protected function initModel(): void
    {
        $this->model = $this->belongsToManyQuery();
    }

    /**
     * 获取关联数据
     * @return Collection
     */
    public function getRelation(): Collection
    {
        return $this->getModel()->get();
    }

    /**
     * 向中间表中附加数据
     * @param mixed $data
     * @param array $pivot
     * @return array|Pivot
     */
    public function save(mixed $data, array $pivot = []): array|Pivot
    {
        return $this->attach($data, $pivot);
    }

    /**
     * 关联预查询
     * @param CollectionInterface|Model $resultSet
     * @param string $relation
     * @param mixed $option
     * @return void
     */
    public function eagerlyResultSet(CollectionInterface|Model $resultSet, string $relation, mixed $option): void
    {
        $model = $this->newModel();

        if ($resultSet instanceof Model) {
            $resultSet = new Collection([$resultSet]);
        }

        $localKeyValues = $resultSet->filter(fn(Model $item) => isset($item->{$this->localKey}))->map(fn(Model $item) => $item->{$this->localKey})
            ->unique()
            ->toArray();

        if (empty($localKeyValues)) {
            return;
        }

        if ($option instanceof Closure) {
            $option($model);
        } else if (is_array($option) && !empty($option)) {
            $model->with($option);
        }

        $data = $model->whereIn($this->foreignKey, $localKeyValues)->get();

        if ($data->isNotEmpty()) {
            /** @var Model $result */
            foreach ($resultSet as $result) {
                $result->{$relation} = $data->where($this->foreignKey, $result->{$this->localKey});
            }
        }
    }

    /**
     * 向中间表中附加数据
     * @param mixed $data
     * @param array $pivot
     * @return Pivot[]|Pivot
     */
    public function attach(mixed $data, array $pivot = []): array|Pivot
    {
        $id = null;
        if (is_array($data)) {
            if (Arr::isList($data)) {
                $id = $data;
            } else {
                $model = $this->newModel();
                $model->save($data);
                $id = $model->getPrimaryValue();
            }
        } else if (is_numeric($data) || is_string($data)) {
            $id = $data;
        } else if ($data instanceof Model) {
            $id = $data->getPrimaryValue();
        }

        if (empty($id)) {
            throw new RuntimeException('miss relation data');
        }

        $pivot[$this->localKey] = $this->parent->getPrimaryValue();
        $ids = (array)$id;
        $result = [];
        foreach ($ids as $id) {
            $pivot[$this->foreignKey] = $id;
            $newPivot = $this->newPivot($pivot);
            if ($newPivot->save()) {
                $result[] = $newPivot;
            }
        }

        if (count($result) == 1) {
            return current($result);
        }

        return $result;
    }

    /**
     * 是否存在关联数据
     * @param mixed $data
     * @return Pivot|null
     */
    public function attached(mixed $data): ?Pivot
    {
        if ($data instanceof Model) {
            $id = $data->getPrimaryValue();
        } else {
            $id = $data;
        }

        /** @var Pivot|null $pivot */
        $pivot = $this->newPivot()->where([
            $this->localKey => $this->parent->getPrimaryValue(),
            $this->foreignKey => $id
        ])->first();

        return $pivot;
    }

    /**
     * 解除关联中间表的数据
     * @param mixed|null $data
     * @param bool $relationDel 是否同时删除关联表数据
     * @return int
     */
    public function detach(mixed $data = null, bool $relationDel = false): int
    {
        $id = null;
        if (is_array($data) || is_string($data) || is_numeric($data)) {
            $id = $data;
        } else if ($data instanceof Model) {
            $id = $data->getPrimaryValue();
        }

        $where = [[$this->localKey, '=', $this->parent->getPrimaryValue()]];

        $emptyId = empty($id);

        if (!$emptyId) {
            $where[] = [$this->foreignKey, is_array($id) ? 'in' : '=', $id];
        }

        $result = $this->newPivot()->newQuery()->where($where)->delete();

        if (!$emptyId && $relationDel) {
            $this->model::destroy($id);
        }

        return $result;
    }

    /**
     * @return Model
     */
    public function belongsToManyQuery(): Model
    {
        $model = $this->newModel();
        $ids = $this->newPivot()->where($this->localKey, $this->parent->getPrimaryValue())->pluck($this->foreignKey)->toArray();
        $model->whereIn($model->getPrimaryKey(), $ids);
        return $model;
    }

    /**
     * 实例化中间模型
     * @param array $data
     * @return Pivot
     */
    public function newPivot(array $data = []): Pivot
    {
        if (!class_exists($this->middleClass)) {
            return new Pivot($data, $this->middleClass);
        }

        return new $this->middleClass($data);
    }
}