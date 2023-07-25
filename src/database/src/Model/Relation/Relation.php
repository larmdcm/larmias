<?php

declare(strict_types=1);

namespace Larmias\Database\Model\Relation;

use Larmias\Database\Model;

abstract class Relation
{
    /**
     * 父级模型
     * @var Model
     */
    protected Model $parent;

    /**
     * 关联模型
     * @var Model
     */
    protected Model $model;

    /**
     * 关联模型类名
     * @var string
     */
    protected string $modelClass;

    /**
     * 关联外键
     * @var string
     */
    protected string $foreignKey;

    /**
     * 关联主键
     * @var string
     */
    protected string $localKey;

    /**
     * 是否已初始化模型
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
            $this->initModel();
            $this->initModel = true;
        }

        return $this->model;
    }

    /**
     * @param Model $model
     * @return self
     */
    public function setModel(Model $model): self
    {
        $this->model = $model;
        return $this;
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
     * @return mixed
     */
    public function __call(string $method, array $args): mixed
    {
        $model = $this->getModel();

        $result = $model->{$method}(...$args);

        if ($result instanceof $model && $result->isDealQuery()) {
            return $this->setModel($result);
        }

        return $result;
    }
}