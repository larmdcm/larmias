<?php

declare(strict_types=1);

namespace Larmias\Support\Reflection;

use ReflectionProperty;
use RuntimeException;
use function count;
use function in_array;
use function is_file;
use function is_readable;
use function token_get_all;
use const T_CLASS;
use const T_NAME_QUALIFIED;
use const T_NAMESPACE;
use const T_STRING;
use const T_WHITESPACE;

class ReflectUtil
{
    /**
     * @var array
     */
    protected static array $cache = [];

    /**
     * 获取对象类名
     * @param object|string $object
     * @return string
     */
    public static function getClassName(object|string $object): string
    {
        return is_object($object) ? get_class($object) : $object;
    }

    /**
     * 判断类是否实现了某个接口
     * @param mixed $class
     * @param string $interface
     * @return bool
     */
    public static function classHasImplement(mixed $class, string $interface): bool
    {
        $className = is_object($class) ? get_class($class) : $class;
        $key = 'class.interface.' . $className;
        if (!isset(static::$cache[$key])) {
            static::$cache[$key] = class_implements($class) ?: [];
        }

        return in_array($interface, static::$cache[$key]);
    }

    /**
     * 设置对象属性
     * @param ReflectionProperty $refProperty
     * @param object $object
     * @param mixed $value
     * @return void
     */
    public static function setProperty(ReflectionProperty $refProperty, object $object, mixed $value): void
    {
        if (!$refProperty->isPublic()) {
            $refProperty->setAccessible(true);
        }
        $refProperty->setValue($object, $value);
    }

    /**
     * 获取对象属性
     * @param ReflectionProperty $refProperty
     * @param object $object
     * @return mixed
     */
    public static function getProperty(ReflectionProperty $refProperty, object $object): mixed
    {
        if (!$refProperty->isPublic()) {
            $refProperty->setAccessible(true);
        }

        if (!$refProperty->isInitialized($object)) {
            return null;
        }

        return $refProperty->getValue($object);
    }

    /**
     * 获取属性名称和类型map
     * @param object|string $object
     * @param array $ignore
     * @param bool $propertyObj
     * @return array
     * @throws \ReflectionException
     */
    public static function getPropertyAttrs(object|string $object, array $ignore = [], bool $propertyObj = false): array
    {
        $vars = [];
        $key = 'class.propertyAttrs.' . static::getClassName($object);
        if (isset(static::$cache[$key])) {
            return static::$cache[$key];
        }

        $refClass = ReflectionManager::reflectClass($object);
        foreach ($refClass->getProperties() as $property) {
            $name = $property->getName();
            if (in_array($name, $ignore)) {
                continue;
            }
            $vars[$name] = 'mixed';
            $propertyType = $property->getType();
            if ($propertyType) {
                $vars[$name] = $propertyObj ? $propertyType : $propertyType->getName();
            }
        }

        return static::$cache[$key] = $vars;
    }

    /**
     * 获取属性变量map
     * @param object|string $object
     * @param array $ignore
     * @param bool $filterNull
     * @param bool $force
     * @return array
     * @throws \ReflectionException
     */
    public static function getPropertyVars(object|string $object, array $ignore = [], bool $filterNull = false, bool $force = false): array
    {
        $attrs = static::getPropertyAttrs($object, $ignore, $force);
        $data = [];
        foreach ($attrs as $name => $type) {
            if ($type instanceof ReflectionProperty) {
                $value = static::getProperty($type, $object);
            } else {
                $value = $object->{$name} ?? null;
            }

            if (is_null($value) && $filterNull) {
                continue;
            }
            $data[$name] = static::typeOf($value, $type);
        }

        return $data;
    }

    /**
     * 设置对象属性
     * @param object $object
     * @param array $data
     * @param array $ignore
     * @param bool $force
     * @return void
     * @throws \ReflectionException
     */
    public static function setPropertyVars(object $object, array $data, array $ignore = [], bool $force = false): void
    {
        $attrs = static::getPropertyAttrs($object, $ignore, $force);
        foreach ($attrs as $name => $type) {
            $value = $data[$name] ?? null;
            if ($type instanceof ReflectionProperty) {
                static::setProperty($type, $object, static::typeOf($value, $type->getType()->getName()));
            } else {
                $object->{$name} = static::typeOf($value, $type);
            }
        }
    }

    /**
     * 类型转换
     * @param mixed $value
     * @param mixed $type
     * @return mixed
     */
    public static function typeOf(mixed $value, ?string $type = null): mixed
    {
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
            case 'object':
                $value = (object)$value;
                break;
            case 'array':
                $value = (array)$value;
                break;
        }

        return $value;
    }

    /**
     * 获取文档注释
     * @param string|object $object
     * @param string|null $method
     * @return string
     * @throws \ReflectionException
     */
    public static function getDocComment(string|object $object, ?string $method = null): string
    {
        if ($method) {
            return ReflectionManager::reflectMethod($object, $method)->getDocComment();
        }
        return ReflectionManager::reflectClass($object)->getDocComment();
    }

    /**
     * 获取文件中的所有类
     * @param string $file
     * @return array
     */
    public static function getAllClassesInFile(string $file): array
    {
        if (!is_file($file) || !is_readable($file)) {
            throw new RuntimeException('Not a file or a readable file: ' . $file);
        }
        $classes = [];
        $tokens = token_get_all(file_get_contents($file));
        $count = count($tokens);
        $tNamespace = [T_NAME_QUALIFIED, T_STRING];
        $namespace = '';

        for ($i = 2; $i < $count; $i++) {
            if ($tokens[$i - 2][0] === T_NAMESPACE && $tokens[$i - 1][0] === T_WHITESPACE) {
                $namespace = '';
                if (!in_array($tokens[$i][0], $tNamespace)) {
                    continue;
                }
                $tempNamespace = $tokens[$i][1] ?? '';
                for ($j = $i + 1; $j < $count; $j++) {
                    if ($tokens[$j] === '{' || $tokens[$j] === ';') {
                        break;
                    }
                    if (in_array($tokens[$j][0], $tNamespace)) {
                        $tempNamespace .= '\\' . $tokens[$j][1];
                    }
                }
                $namespace = $tempNamespace;
            } else if ($tokens[$i - 2][0] === T_CLASS && $tokens[$i - 1][0] === T_WHITESPACE && $tokens[$i][0] === T_STRING) {
                $classes[] = ($namespace ? $namespace . '\\' : '') . $tokens[$i][1];
            }
        }

        return $classes;
    }

    /**
     * 检查文件是否存在语法错误
     * @param string $file
     * @param array $options
     * @return string|null
     */
    public static function checkFileSyntaxError(string $file, array $options = []): ?string
    {
        $bin = $options['bin'] ?? PHP_BINARY;
        if (!$bin || !is_file($bin) || !is_executable($bin)) {
            return 'The executable bin does not exist:' . $bin;
        }
        exec(sprintf('%s -l %s 2>&1', $bin, $file), $outputs, $status);
        if ($status != 0) {
            return array_filter($outputs)[0] ?? 'Parse error: syntax error';
        }
        return null;
    }
}