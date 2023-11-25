<?php

declare(strict_types=1);

namespace Larmias\Di\Annotation;

use Larmias\Support\Reflection\ReflectionManager;
use ReflectionProperty;
use ReflectionException;

abstract class AbstractAnnotation
{
    /**
     * @return array
     * @throws ReflectionException
     */
    public function toArray(): array
    {
        $properties = ReflectionManager::reflectClass(static::class)->getProperties(ReflectionProperty::IS_PUBLIC);
        $result = [];
        foreach ($properties as $property) {
            $result[$property->getName()] = $property->getValue($this);
        }
        return $result;
    }
}