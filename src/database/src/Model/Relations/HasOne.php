<?php

declare(strict_types=1);

namespace Larmias\Database\Model\Relations;

use Closure;
use Larmias\Database\Model;
use Larmias\Database\Model\Contracts\CollectionInterface;

class HasOne extends OneToOne
{
    /**
     * 初始化查询
     * @return void
     */
    protected function initQuery(): void
    {
        $this->query->where($this->getForeignKey(), $this->parent->getAttribute($this->getLocalKey()));
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
        $query = $this->newModel()->newQuery();

        $localKeyValues = $resultSet->filter(fn(Model $item) => isset($item->{$this->localKey}))
            ->map(fn(Model $item) => $item->{$this->localKey})
            ->unique()
            ->toArray();

        if (empty($localKeyValues)) {
            return;
        }

        if ($option instanceof Closure) {
            $option($query);
        } else if (is_array($option) && !empty($option)) {
            $query->with($option);
        }

        $data = $query->whereIn($this->foreignKey, $localKeyValues)->get()->pluck(null, $this->foreignKey);

        if ($data->isNotEmpty()) {
            /** @var Model $result */
            foreach ($resultSet as $result) {
                $pk = $result->{$this->localKey};
                $result->setRelation($relation, $data[$pk] ?? null);
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