<?php

declare(strict_types=1);

namespace Larmias\Database\Model\Relation;

use Larmias\Database\Model;

abstract class Relation
{
    /**
     * @var Model
     */
    protected Model $parent;

    /**
     * @var Model
     */
    protected Model $model;

    /**
     * @var string
     */
    protected string $modelClass;

    /**
     * @var string
     */
    protected string $foreignKey;

    /**
     * @var string
     */
    protected string $localKey;

    /**
     * @var bool
     */
    protected bool $initModel = false;

    /**
     * @return Model
     */
    public function getParent(): Model
    {
        return $this->parent;
    }

    /**
     * @param Model $parent
     */
    public function setParent(Model $parent): void
    {
        $this->parent = $parent;
    }

    /**
     * @return Model
     */
    public function getModel(): Model
    {
        if (!$this->initModel) {
            $this->initModel = true;
            $this->initModel();
        }
        return $this->model;
    }

    /**
     * @param Model $model
     */
    public function setModel(Model $model): void
    {
        $this->model = $model;
    }

    /**
     * @return string
     */
    public function getModelClass(): string
    {
        return $this->modelClass;
    }

    /**
     * @param string $modelClass
     */
    public function setModelClass(string $modelClass): void
    {
        $this->modelClass = $modelClass;
    }

    /**
     * @return string
     */
    public function getForeignKey(): string
    {
        return $this->foreignKey;
    }

    /**
     * @param string $foreignKey
     */
    public function setForeignKey(string $foreignKey): void
    {
        $this->foreignKey = $foreignKey;
    }

    /**
     * @return string
     */
    public function getLocalKey(): string
    {
        return $this->localKey;
    }

    /**
     * @param string $localKey
     */
    public function setLocalKey(string $localKey): void
    {
        $this->localKey = $localKey;
    }

    /**
     * @return void
     */
    protected function initModel(): void
    {
    }

    /**
     * @return Model
     */
    protected function newModel(): Model
    {
        return new $this->modelClass;
    }

    /**
     * @param string $method
     * @param array $args
     * @return Relation
     */
    public function __call(string $method, array $args)
    {
        $model = $this->getModel();

        $result = $model->{$method}(...$args);

        return $result instanceof $model ? $this : $result;
    }
}