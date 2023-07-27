<?php

declare(strict_types=1);

namespace Larmias\Database\Model\Relations;

use Larmias\Database\Model\AbstractModel;

abstract class OneToOne extends Relation
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

    /**z
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