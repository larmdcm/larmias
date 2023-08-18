<?php

declare(strict_types=1);

namespace Larmias\Database\Model\Relations;

use Closure;
use Larmias\Database\Model\Contracts\QueryInterface;
use Larmias\Database\Model\Contracts\CollectionInterface;
use Larmias\Database\Model\Model;
use Larmias\Database\Model\Pivot;
use Larmias\Utils\Arr;
use RuntimeException;
use function is_array;
use function is_numeric;
use function is_string;
use function method_exists;

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
        $this->query = $this->newModel()->newQuery();
    }

    /**
     * 初始化模型查询
     * @return void
     */
    protected function initQuery(): void
    {
        $this->query = $this->belongsToManyQuery([
            ['pivot.' . $this->localKey, '=', $this->parent->getPrimaryValue()]
        ]);
    }

    /**
     * 获取关联数据
     * @return CollectionInterface
     */
    public function getRelation(): CollectionInterface
    {
        $collect = $this->query()->get();
        return $this->matchPivot($collect);
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
     * @param CollectionInterface $resultSet
     * @param string $relation
     * @param mixed $option
     * @return void
     */
    public function eagerlyResultSet(CollectionInterface $resultSet, string $relation, mixed $option): void
    {
        if ($resultSet->isEmpty()) {
            return;
        }

        $primaryKey = $resultSet[0]->getPrimaryKey();

        $primaryValues = $resultSet->filter(fn(Model $item) => isset($item->{$primaryKey}))
            ->map(fn(Model $item) => $item->{$primaryKey})
            ->unique()
            ->toArray();

        if (empty($primaryValues)) {
            return;
        }

        $query = $this->belongsToManyQuery([
            ['pivot.' . $this->localKey, 'in', $primaryValues]
        ]);

        if ($option instanceof Closure) {
            $option($query);
        } else if (is_array($option) && !empty($option)) {
            $query->with($option);
        }

        $data = $query->get();

        if ($data->isNotEmpty()) {
            /** @var Model $result */
            foreach ($resultSet as $result) {
                $result->setRelation($relation, $this->matchPivot($data->where('pivot__' . $this->localKey, $result->{$primaryKey})));
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
                $model = $this->newModel($data);
                $model->save();
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

        if (!$emptyId && $relationDel && method_exists($this->modelClass, 'destroy')) {
            $this->modelClass::destroy($id);
        }

        return $result;
    }

    /**
     * @param array $where
     * @return QueryInterface
     */
    public function belongsToManyQuery(array $where): QueryInterface
    {
        $model = $this->newModel();
        $pivot = $this->newPivot();
        $field = sprintf('%s.*,pivot.%s pivot__%s,pivot.%s pivot__%s', $model->getTable(),
            $this->foreignKey, $this->foreignKey, $this->localKey, $this->localKey);
        return $model->alias($model->getTable())
            ->field($field)
            ->join([$pivot->getTable() => 'pivot'], sprintf('pivot.%s = %s.%s', $this->foreignKey, $model->getTable(), $model->getPrimaryKey()))
            ->where($where);
    }

    /**
     * 匹配中间模型
     * @param CollectionInterface $collect
     * @return CollectionInterface
     */
    protected function matchPivot(CollectionInterface $collect): CollectionInterface
    {
        /** @var CollectionInterface $collect */
        $collect = $collect->each(function (Model $model) {
            $pivot = [];
            $data = $model->getData();

            foreach ($data as $field => $value) {
                if (str_contains($field, '__')) {
                    [$name, $attr] = explode('__', $field);
                    if ($name == 'pivot') {
                        $pivot[$attr] = $value;
                    }
                    unset($model->{$field});
                }
            }

            $model->setRelation('pivot', $this->newPivot($pivot));
        });

        return $collect;
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