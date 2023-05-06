<?php

declare(strict_types=1);

namespace Larmias\Database\Model\Relation;

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
        $this->model = $this->newModel();
    }

    /**
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