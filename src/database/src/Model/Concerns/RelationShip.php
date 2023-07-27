<?php

declare(strict_types=1);

namespace Larmias\Database\Model\Concerns;

use Larmias\Database\Model\AbstractModel;
use Larmias\Database\Model\Relations\BelongsTo;
use Larmias\Database\Model\Relations\BelongsToMany;
use Larmias\Database\Model\Relations\HasMany;
use Larmias\Database\Model\Relations\HasOne;
use Larmias\Utils\Str;
use function Larmias\Utils\class_basename;
use function str_contains;

/**
 * @mixin AbstractModel
 */
trait RelationShip
{
    /**
     * 一对一关联
     * @param string $modelClass
     * @param string $foreignKey
     * @param string $localKey
     * @return HasOne
     */
    public function hasOne(string $modelClass, string $foreignKey = '', string $localKey = ''): HasOne
    {
        $foreignKey = $foreignKey ?: $this->getForeignKey($this->name);
        $localKey = $localKey ?: $this->getPrimaryKey();
        return new HasOne($this, $modelClass, $foreignKey, $localKey);
    }

    /**
     * 反向一对一关联
     * @param string $modelClass
     * @param string $foreignKey
     * @param string $localKey
     * @return BelongsTo
     */
    public function belongsTo(string $modelClass, string $foreignKey = '', string $localKey = ''): BelongsTo
    {
        $foreignKey = $foreignKey ?: $this->getForeignKey($modelClass);
        $localKey = $localKey ?: (new $modelClass)->getPrimaryKey();
        return new BelongsTo($this, $modelClass, $foreignKey, $localKey);
    }

    /**
     * 一对多关联
     * @param string $modelClass
     * @param string $foreignKey
     * @param string $localKey
     * @return HasMany
     */
    public function hasMany(string $modelClass, string $foreignKey = '', string $localKey = ''): HasMany
    {
        $foreignKey = $foreignKey ?: $this->getForeignKey($this->name);
        $localKey = $localKey ?: $this->getPrimaryKey();
        return new HasMany($this, $modelClass, $foreignKey, $localKey);
    }

    /**
     * 多对多关联
     * @param string $modelClass
     * @param string $middleClass
     * @param string $foreignKey
     * @param string $localKey
     * @return BelongsToMany
     */
    public function belongsToMany(string $modelClass, string $middleClass, string $foreignKey = '', string $localKey = ''): BelongsToMany
    {
        $foreignKey = $foreignKey ?: Str::snake(class_basename($modelClass)) . '_id';
        $localKey = $localKey ?: $this->getForeignKey($this->name);
        return new BelongsToMany($this, $modelClass, $middleClass, $foreignKey, $localKey);
    }

    /**
     * 是否为关联属性
     * @param string $name
     * @return string|null
     */
    public function isRelationAttr(string $name): ?string
    {
        $name = Str::camel($name);
        if (method_exists($this, $name) && !method_exists(AbstractModel::class, $name)) {
            return $name;
        }

        return null;
    }

    /**
     * 获取外键key
     * @param string $name
     * @return string
     */
    protected function getForeignKey(string $name): string
    {
        if (str_contains($name, '\\')) {
            $name = class_basename($name);
        }

        return Str::snake($name) . '_id';
    }
}