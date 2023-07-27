<?php

declare(strict_types=1);

namespace Larmias\Database\Model\Relations;

use Closure;
use Larmias\Contracts\CollectionInterface;
use Larmias\Database\Model\AbstractModel;

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

        $localKeyValues = $resultSet->filter(fn(AbstractModel $item) => isset($item->{$this->localKey}))
            ->map(fn(AbstractModel $item) => $item->{$this->localKey})
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
            /** @var AbstractModel $result */
            foreach ($resultSet as $result) {
                $pk = $result->{$this->localKey};
                $result->setRelation($relation, $data[$pk] ?? null);
            }
        }
    }

    /**
     * 获取关联数据
     * @return AbstractModel|null
     */
    public function getRelation(): ?AbstractModel
    {
        /** @var AbstractModel $model */
        $model = $this->query()->first();
        return $model;
    }
}