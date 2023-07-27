<?php

declare(strict_types=1);

namespace Larmias\Database\Model\Relations;

use Closure;
use Larmias\Contracts\CollectionInterface;
use Larmias\Database\Contracts\ModelCollectionInterface;
use Larmias\Database\Model\AbstractModel;

class HasMany extends Relation
{
    /**
     * @param AbstractModel $parent
     * @param string $modelClass
     * @param string $foreignKey
     * @param string $localKey
     */
    public function __construct(protected AbstractModel $parent, protected string $modelClass, protected string $foreignKey, protected string $localKey)
    {
        $this->query = $this->newModel()->newQuery();
    }

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
        $model = $this->newModel();

        $localKeyValues = $resultSet->filter(fn(AbstractModel $item) => isset($item->{$this->localKey}))
            ->map(fn(AbstractModel $item) => $item->{$this->localKey})
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
            /** @var AbstractModel $result */
            foreach ($resultSet as $result) {
                $result->setRelation($relation, $data->where($this->foreignKey, $result->{$this->localKey}));
            }
        }
    }

    /**
     * 获取关联数据
     * @return ModelCollectionInterface
     */
    public function getRelation(): ModelCollectionInterface
    {
        /** @var ModelCollectionInterface $collect */
        $collect = $this->query()->get();
        return $collect;
    }

    /**
     * 保存关联数据
     * @param array $data
     * @return AbstractModel|null
     */
    public function save(array $data = []): ?AbstractModel
    {
        $model = $this->newModel($data);
        $model->setAttribute($this->getForeignKey(), $this->parent->getAttribute($this->getLocalKey()));
        return $model->save() ? $model : null;
    }
}