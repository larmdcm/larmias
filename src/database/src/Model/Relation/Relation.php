<?php

declare(strict_types=1);

namespace Larmias\Database\Model\Relation;

use Larmias\Database\Model;
use Larmias\Utils\Contracts\Arrayable;
use Larmias\Utils\Contracts\Jsonable;
use ArrayAccess;
use Stringable;
use JsonSerializable;

/**
 * @mixin Model
 */
abstract class Relation implements ArrayAccess, Arrayable, Jsonable, Stringable, JsonSerializable
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
     * @return string|int
     */
    protected function getParentId(): string|int
    {
        return $this->parent->getAttribute($this->parent->getPrimaryKey());
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

        return $result instanceof $model ? $this->setModel($result) : $result;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * @param int $options
     * @return string
     */
    public function toJson(int $options = JSON_UNESCAPED_UNICODE): string
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get(string $name): mixed
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function __set(string $name, mixed $value): void
    {
        $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * @param string $name
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * @param string $name
     * @return void
     */
    public function __unset(string $name): void
    {
        $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    // JsonSerializable
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    // ArrayAccess
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        $this->__call(__FUNCTION__, func_get_args());
    }

    #[\ReturnTypeWillChange]
    public function offsetExists($offset): bool
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        $this->__call(__FUNCTION__, func_get_args());
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }
}