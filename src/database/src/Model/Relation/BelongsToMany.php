<?php

declare(strict_types=1);

namespace Larmias\Database\Model\Relation;

use Larmias\Database\Model;
use Larmias\Utils\Arr;
use Larmias\Database\Model\Collection;
use RuntimeException;
use function is_array;
use function is_numeric;
use function is_string;

class BelongsToMany extends Relation
{
    /**
     * 中间表模型
     * @var Model
     */
    protected Model $pivot;

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
        $this->pivot = $this->newPivot();
    }

    /**
     * 初始化模型查询
     * @return void
     */
    protected function initModel(): void
    {
        $ids = $this->pivot->where($this->localKey, $this->parent->getPrimaryValue())->pluck($this->foreignKey);
        $this->model->whereIn($this->model->getPrimaryKey(), $ids);
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
     * 保存关联数据
     * @param mixed $data
     * @param array $pivot
     * @return array
     */
    public function save(mixed $data, array $pivot): array
    {
        return $this->attach($data, $pivot);
    }

    /**
     * 向中间表中附加数据
     * @param mixed $data
     * @param array $pivot
     * @return array
     */
    public function attach(mixed $data, array $pivot = []): array
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
            $result[] = $this->pivot->setExists(false)->data([])->save($pivot);
        }

        if (count($result) == 1) {
            return current($result);
        }

        return $result;
    }

    /**
     * 是否存在关联数据
     * @param mixed $data
     * @return Model|null
     */
    public function attached(mixed $data): ?Model
    {
        if ($data instanceof Model) {
            $id = $data->getPrimaryValue();
        } else {
            $id = $data;
        }

        return $this->pivot->where([
            $this->localKey => $this->parent->getPrimaryValue(),
            $this->foreignKey => $id
        ])->first();
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

        $result = $this->pivot->newQuery()->where($where)->delete();

        if (!$emptyId && $relationDel) {
            $this->model::destroy($id);
        }

        return $result;
    }

    /**
     * 实例化中间模型
     * @param array $data
     * @return Model
     */
    public function newPivot(array $data = []): Model
    {
        return new $this->middleClass($data);
    }
}