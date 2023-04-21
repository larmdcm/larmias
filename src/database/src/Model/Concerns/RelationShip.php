<?php

declare(strict_types=1);

namespace Larmias\Database\Model\Concerns;

use Larmias\Database\Model;
use Larmias\Database\Model\Relation\HasOne;
use Larmias\Utils\Str;
use function Larmias\Utils\class_basename;
use function str_contains;

/**
 * @mixin Model
 */
trait RelationShip
{
    /**
     * has one
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
     * @param string $name
     * @return string|null
     */
    public function isRelationAttr(string $name): ?string
    {
        $name = Str::camel($name);
        if (method_exists($this, $name) && !method_exists(Model::class, $name)) {
            return $name;
        }

        return null;
    }


    /**
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