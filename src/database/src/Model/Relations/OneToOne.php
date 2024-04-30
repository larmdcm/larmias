<?php

declare(strict_types=1);

namespace Larmias\Database\Model\Relations;

use Larmias\Database\Model;

abstract class OneToOne extends Relation
{
    /**
     * @param Model $parent
     * @param string $modelClass
     * @param string $foreignKey
     * @param string $localKey
     */
    public function __construct(protected Model $parent, protected string $modelClass, protected string $foreignKey, protected string $localKey)
    {
        $this->query = $this->newModel()->newQuery();
    }

    /**
     * 保存关联数据
     * @param array $data
     * @return Model|null
     */
    public function save(array $data = []): ?Model
    {
        $model = $this->newModel($data);
        $model->setAttribute($this->getForeignKey(), $this->parent->getAttribute($this->getLocalKey()));
        return $model->save() ? $model : null;
    }
}