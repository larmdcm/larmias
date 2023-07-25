<?php

declare(strict_types=1);

namespace Larmias\Database\Model\Relation;

use Larmias\Contracts\CollectionInterface;
use Larmias\Database\Model;
use Larmias\Database\Model\Collection;
use Closure;

class HasOne extends OneToOne
{
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

        $data = $model->whereIn($this->foreignKey, $localKeyValues)->get()->pluck(null, $this->foreignKey);

        if ($data->isNotEmpty()) {
            /** @var Model $result */
            foreach ($resultSet as $result) {
                $pk = $result->{$this->localKey};
                $result->{$relation} = $data[$pk] ?? null;
            }
        }
    }

    /**
     * 获取关联数据
     * @return Model|null
     */
    public function getRelation(): ?Model
    {
        return $this->getModel()->first();
    }
}