<?php

declare(strict_types=1);

namespace Larmias\Database\Model\Relations;

use Closure;
use Larmias\Contracts\CollectionInterface;
use Larmias\Database\Model;

class BelongsTo extends OneToOne
{
    /**
     * 初始化查询
     * @return void
     */
    public function initQuery(): void
    {
        $this->query->where($this->getLocalKey(), $this->parent->getAttribute($this->getForeignKey()));
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
        $model = $this->newModel();

        $foreignKeyValues = $resultSet->filter(fn(Model $item) => isset($item->{$this->foreignKey}))
            ->map(fn(Model $item) => $item->{$this->foreignKey})
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
                $result->setRelation($relation, $data[$key] ?? null);
            }
        }
    }

    /**
     * 获取关联数据
     * @return Model|null
     */
    public function getRelation(): ?Model
    {
        /** @var Model $model */
        $model = $this->query()->first();
        return $model;
    }
}