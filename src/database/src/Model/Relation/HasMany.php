<?php

declare(strict_types=1);

namespace Larmias\Database\Model\Relation;

use Larmias\Contracts\CollectionInterface;
use Larmias\Database\Model;
use Larmias\Database\Model\Collection;
use Closure;

class HasMany extends Relation
{
    /**
     * @param Model $parent
     * @param string $modelClass
     * @param string $foreignKey
     * @param string $localKey
     */
    public function __construct(protected Model $parent, protected string $modelClass, protected string $foreignKey, protected string $localKey)
    {
        $this->model = $this->newModel();
    }

    /**
     * 初始化模型查询
     * @return void
     */
    protected function initModel(): void
    {
        $this->model->where($this->getForeignKey(), $this->parent->getAttribute($this->getLocalKey()));
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
     * 获取关联数据
     * @return Collection
     */
    public function getRelation(): Collection
    {
        return $this->getModel()->get();
    }

    /**
     * 保存关联数据
     * @param array $data
     * @return Model|null
     */
    public function save(array $data = []): ?Model
    {
        $model = $this->newModel();
        $model->setAttribute($this->getForeignKey(), $this->parent->getAttribute($this->getLocalKey()));
        return $model->save($data) ? $model : null;
    }
}