<?php

declare(strict_types=1);

namespace Larmias\Database\Model\Relation;

use Larmias\Database\Model;

class HasOne extends Relation
{
    /**
     * @param Model $parent
     * @param string $modelClass
     * @param string $foreignKey
     * @param string $localKey
     */
    public function __construct(protected Model $parent, protected string $modelClass, protected string $foreignKey, protected string $localKey)
    {
    }

    /**
     * @return void
     */
    public function initModel(): void
    {
        $this->model = $this->newModel();
        $this->model->where($this->foreignKey, $this->getId());
    }

    /**
     * @return Model|null
     */
    public function getRelation(): ?Model
    {
        return $this->getModel()->first();
    }

    /**
     * @param array $data
     * @return bool
     */
    public function save(array $data = []): bool
    {
        $model = $this->newModel();
        $model->setAttribute($this->foreignKey, $this->getId());
        return $model->save($data);
    }

    /**
     * @return string|int
     */
    protected function getId(): string|int
    {
        return $this->parent->getAttribute($this->parent->getPrimaryKey());
    }
}