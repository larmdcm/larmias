<?php

declare(strict_types=1);

namespace Larmias\Support\Reflection;

use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;
use function get_class;
use function is_object;

class ReflectionManager
{
    /**
     * @var array
     */
    protected static array $container = [
        'class' => [],
        'method' => [],
        'property' => []
    ];

    /**
     * @param string|object $object
     * @return ReflectionClass
     * @throws ReflectionException
     */
    public static function reflectClass(string|object $object): ReflectionClass
    {
        $className = is_object($object) ? get_class($object) : $object;
        if (!isset(static::$container['class'][$className])) {
            if (!class_exists($className) && !interface_exists($className) && !trait_exists($className)) {
                throw new InvalidArgumentException("Class {$className} not exist");
            }
            static::$container['class'][$className] = new ReflectionClass($object);
        }
        return static::$container['class'][$className];
    }

    /**
     * @param string|object $object
     * @param string $method
     * @param bool $staticClass
     * @return ReflectionMethod
     * @throws ReflectionException
     */
    public static function reflectMethod(string|object $object, string $method, bool $staticClass = false): ReflectionMethod
    {
        $className = is_object($object) ? get_class($object) : $object;
        $key = $className . '::' . $method;
        if (!isset(static::$container['method'][$key])) {
            if (!class_exists($className)) {
                throw new InvalidArgumentException("Class {$className} not exist");
            }
            static::$container['method'][$key] = $staticClass ? new ReflectionMethod($className, $method) : static::reflectClass($object)->getMethod($method);
        }
        return static::$container['method'][$key];
    }

    /**
     * @param string|object $object
     * @param string $property
     * @return ReflectionProperty
     * @throws ReflectionException
     */
    public static function reflectProperty(string|object $object, string $property): ReflectionProperty
    {
        $className = is_object($object) ? get_class($object) : $object;
        $key = $className . '::' . $property;
        if (!isset(static::$container['property'][$key])) {
            if (!class_exists($className)) {
                throw new InvalidArgumentException("Class {$className} not exist");
            }
            static::$container['property'][$key] = static::reflectClass($object)->getProperty($property);
        }
        return static::$container['property'][$key];
    }
}