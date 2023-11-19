<?php

declare(strict_types=1);

namespace Larmias\Database\Model\Relations;

use Larmias\Database\Model\Model;
use Larmias\Database\Model\Contracts\QueryInterface;

/**
 * @method QueryInterface with(string|array $with)
 */
abstract class Relation
{
    /**
     * 父级模型
     * @var Model
     */
    protected Model $parent;

    /**
     * @var QueryInterface
     */
    protected QueryInterface $query;

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
     * 是否已初始化查询
     * @var bool
     */
    protected bool $initQuery = false;

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
    protected function initQuery(): void
    {
    }

    /**
     * 实例化模型
     * @param array $data
     * @return Model
     */
    protected function newModel(array $data = []): Model
    {
        return new $this->modelClass($data);
    }

    /**
     * @return QueryInterface
     */
    public function query(): QueryInterface
    {
        if (!$this->initQuery) {
            $this->initQuery();
            $this->initQuery = true;
        }

        return $this->query;
    }

    /**
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function __call(string $method, array $args): mixed
    {
        $query = $this->query();

        $result = $query->{$method}(...$args);

        if ($result instanceof $query) {
            return $this;
        }

        return $result;
    }
}