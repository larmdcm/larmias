<?php

declare(strict_types=1);

namespace Larmias\Database\Model\Relations;

use Larmias\Contracts\CollectionInterface;
use Larmias\Database\Model;
use Larmias\Database\Model\Collection;
use Closure;

class BelongsTo extends OneToOne
{
    /**
     * 初始化模型查询
     * @return void
     */
    public function initModel(): void
    {
        $this->model->where($this->getLocalKey(), $this->parent->getAttribute($this->getForeignKey()));
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

        $foreignKeyValues = $resultSet->filter(fn(Model $item) => isset($item->{$this->foreignKey}))->map(fn(Model $item) => $item->{$this->foreignKey})
            ->unique()
            ->toArray();

        if (empty($foreignKeyValues)) {
            return;
        }

        if ($option instanceof Closure) {
            $option($model);
        } else if (is_array($option) && !empty($option)) {
            $model->with($option);
        }

        $data = $model->whereIn($this->localKey, $foreignKeyValues)->get()->pluck(null, $this->localKey);

        if ($data->isNotEmpty()) {
            /** @var Model $result */
            foreach ($resultSet as $result) {
                $key = $result->{$this->foreignKey};
                $result->{$relation} = $data[$key] ?? null;
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