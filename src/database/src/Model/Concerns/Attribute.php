<?php

declare(strict_types=1);

namespace Larmias\Database\Model\Concerns;

use InvalidArgumentException;
use Larmias\Database\Model\Contracts\CollectionInterface;
use Larmias\Database\Model\Model;
use Larmias\Database\Model\Relations\Relation;
use Larmias\Utils\Str;
use function array_key_exists;
use function array_udiff_assoc;
use function date;
use function is_numeric;
use function is_object;
use function json_encode;
use function method_exists;
use function serialize;
use function str_contains;
use function strtotime;

/**
 * @mixin Model
 */
trait Attribute
{
    /**
     * 数据
     * @var array
     */
    protected array $data = [];

    /**
     * 原始数据
     * @var array
     */
    protected array $origin = [];

    /**
     * 数据转换
     * @var array
     */
    protected array $cast = [];

    /**
     * 关联数据
     * @var array
     */
    protected array $relation = [];

    /**
     * 可写入的字段
     * @var array
     */
    protected array $fillable = [];

    /**
     * 不可写入的字段
     * @var array
     */
    protected array $guarded = [];

    /**
     * 获取属性数据
     * @param string $name
     * @param bool $strict
     * @return mixed
     */
    public function getAttribute(string $name, bool $strict = true): mixed
    {
        $method = 'get' . Str::studly($name) . 'Attr';
        $relationAttr = $this->isRelationAttr($name);
        $propertyExists = array_key_exists($name, $this->data);
        $hasMethod = method_exists($this, $method);

        if (!$propertyExists && !$relationAttr && !$hasMethod) {
            if (!$strict) {
                return null;
            }
            throw new InvalidArgumentException('property not exists:' . static::class . '->' . $name);
        }

        $value = null;

        if ($propertyExists || $relationAttr) {
            $value = $propertyExists ? $this->data[$name] : $this->getRelationValue($relationAttr);
        }

        if ($hasMethod) {
            $value = $this->{$method}($value, $name);
        }

        if (isset($this->cast[$name])) {
            $value = $this->transformValue($this->cast[$name], $value, true);
        }

        return $value;
    }

    /**
     * 设置属性数据
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function setAttribute(string $name, mixed $value): void
    {
        $method = 'set' . Str::studly($name) . 'Attr';

        if (method_exists($this, $method)) {
            $value = $this->{$method}($value, $name);
        }

        if (isset($this->cast[$name])) {
            $value = $this->transformValue($this->cast[$name], $value);
        }

        $this->data[$name] = $value;
    }

    /**
     * 批量设置属性数据
     * @param array $data
     * @return Model|Attribute
     */
    public function setAttributes(array $data): self
    {
        foreach ($data as $name => $value) {
            $this->setAttribute($name, $value);
        }

        return $this;
    }

    /**
     * 获取数据属性是否存在
     * @param string $name
     * @return bool
     */
    public function hasAttribute(string $name): bool
    {
        return $this->getAttribute($name, false) !== null;
    }

    /**
     * 设置关联属性
     * @param string $name
     * @param Model|CollectionInterface|null $relation
     * @return Attribute|Model
     */
    public function setRelation(string $name, Model|CollectionInterface|null $relation): self
    {
        $this->relation[$name] = $relation;
        return $this;
    }

    /**
     * 刷新原始数据
     * @param array|null $data
     * @return void
     */
    public function refreshOrigin(?array $data = null): void
    {
        if ($data !== null) {
            $this->origin = array_merge($this->data, $data);
        } else {
            $this->origin = $this->data;
        }
    }

    /**
     * 设置数据
     * @param array $data
     * @return Model|Attribute
     */
    public function data(array $data = []): self
    {
        return $this->fill($data, true);
    }

    /**
     * 填充数据
     * @param array $data
     * @param bool $raw
     * @return Model|Attribute
     */
    public function fill(array $data, bool $raw = false): self
    {
        if ($raw) {
            $this->data = $data;
        } else {
            $this->data = [];
            $this->setAttributes($data);
        }

        $this->refreshOrigin();
        if ($this->getPrimaryValue()) {
            $this->exists(true);
        }

        return $this;
    }

    /**
     * 获取数据
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * 设置不可写字段
     * @return array
     */
    public function getFillable(): array
    {
        return $this->fillable;
    }

    /**
     * 设置可写字段
     * @param array $fillable
     * @return Model|Attribute
     */
    public function fillable(array $fillable): self
    {
        $this->fillable = $fillable;

        return $this;
    }

    /**
     * 获取不可写字段
     * @return array
     */
    public function getGuarded(): array
    {
        return $this->guarded;
    }

    /**
     * 设置不可写字段
     * @param array $guarded
     * @return Model|Attribute
     */
    public function guard(array $guarded): self
    {
        $this->guarded = $guarded;
        return $this;
    }

    /**
     * 获取字段是否可写
     * @param string $key
     * @return bool
     */
    public function isFillable(string $key): bool
    {
        if (in_array($key, $this->getFillable())) {
            return true;
        }

        if ($this->isGuarded($key)) {
            return false;
        }

        return empty($this->getFillable());
    }

    /**
     * 字段是否不可写
     * @param string $key
     * @return bool
     */
    public function isGuarded(string $key): bool
    {
        return !empty($this->guarded) && in_array($key, $this->getGuarded());
    }

    /**
     * 获取主键值
     * @return int|string|null
     */
    public function getPrimaryValue(): int|string|null
    {
        $primaryKey = $this->getPrimaryKey();
        return array_key_exists($primaryKey, $this->data) ? $this->data[$primaryKey] : null;
    }

    /**
     * 获取关联值
     * @param string $name
     * @return mixed
     */
    protected function getRelationValue(string $name): mixed
    {
        if (!isset($this->relation[$name])) {
            $relation = $this->{$name}();
            $this->relation[$name] = $relation instanceof Relation ? $relation->getRelation() : null;
        }
        return $this->relation[$name];
    }

    /**
     * 值类型转换
     * @param string $type
     * @param mixed $value
     * @param bool $isGet
     * @return mixed
     */
    protected function transformValue(string $type, mixed $value, bool $isGet = false): mixed
    {
        if ($value === null) {
            return null;
        }

        switch ($type) {
            case 'int':
            case 'integer':
                $value = (int)$value;
                break;
            case 'string':
                $value = (string)$value;
                break;
            case 'double':
            case 'float':
                $value = (float)$value;
                break;
            case 'bool':
            case 'boolean':
                $value = (bool)$value;
                break;
            case 'timestamp':
                if (!is_numeric($value)) {
                    $value = strtotime($value);
                }
                break;
            case 'datetime':
                $value = is_numeric($value) ? $value : strtotime($value);
                $value = date('Y-m-d H:i:s.u', $value);
                break;
            case 'object':
                if ($isGet) {
                    $value = empty($value) ? new \stdClass() : json_decode($value);
                } else {
                    if (is_object($value)) {
                        $value = json_encode($value, JSON_FORCE_OBJECT);
                    }
                }
                break;
            case 'array':
                if ($isGet) {
                    $value = empty($value) ? [] : json_decode($value, true);
                } else {
                    $value = (array)$value;
                }
                break;
            case 'json':
                if ($isGet) {
                    $value = json_decode($value, true);
                } else {
                    $value = json_encode($value, JSON_UNESCAPED_UNICODE);
                }
                break;
            case 'serialize':
                $value = $isGet ? unserialize($value) : serialize($value);
                break;
            default:
                if ($isGet) {
                    if (str_contains($type, '\\')) {
                        $value = new $type($value);
                    }
                } else {
                    if (is_object($value) && str_contains($type, '\\') && $value instanceof \Stringable) {
                        $value = $value->__toString();
                    }
                }
        }

        return $value;
    }

    /**
     * 获取改变的数据
     * @return array
     */
    protected function getChangedData(): array
    {
        return array_udiff_assoc($this->data, $this->origin, function ($a, $b) {
            if ((empty($a) || empty($b)) && $a !== $b) {
                return 1;
            }
            return is_object($a) || $a != $b ? 1 : 0;
        });
    }
}