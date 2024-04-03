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
        return $refProperty->getValue($object);
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
}