<?php

declare(strict_types=1);

namespace Larmias\Database\Model\Relation;

use Larmias\Database\Model;
use Larmias\Database\Model\Collection;

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
     * @return void
     */
    protected function initModel(): void
    {
        $this->model->where($this->getForeignKey(), $this->parent->getAttribute($this->getLocalKey()));
    }

    /**
     * @return Collection
     */
    public function getRelation(): Collection
    {
        return $this->getModel()->get();
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