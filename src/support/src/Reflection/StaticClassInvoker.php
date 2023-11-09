<?php

declare(strict_types=1);

namespace Larmias\Support\Reflection;

use ReflectionMethod;
use ReflectionException;

class StaticClassInvoker
{
    /**
     * @param string|array $object
     * @return ReflectionMethod
     * @throws ReflectionException
     */
    public static function getMethodReflect(string|array $object): ReflectionMethod
    {
        if (is_array($object)) {
            [$class, $method] = $object;
        } else {
            [$class, $method] = explode('::', $object);
        }
        return ReflectionManager::reflectMethod($class, $method);
    }

    /**
     * @param ReflectionMethod $reflect
     * @param array $args
     * @param bool $accessible
     * @return mixed
     * @throws ReflectionException
     */
    public static function invokeMethod(ReflectionMethod $reflect, array $args = [], bool $accessible = false): mixed
    {
        if ($accessible) {
            $reflect->setAccessible($accessible);
        }
        return $reflect->invokeArgs(null, $args);
    }

    /**
     * @param string|array $object
     * @param array $args
     * @param bool $accessible
     * @return mixed
     * @throws ReflectionException
     */
    public static function invoke(string|array $object, array $args = [], bool $accessible = false): mixed
    {
        return static::invokeMethod(static::getMethodReflect($object), $args, $accessible);
    }
}