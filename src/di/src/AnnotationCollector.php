<?php

declare(strict_types=1);

namespace Larmias\Di;

use Larmias\Utils\Arr;

class AnnotationCollector
{
    /**
     * @var array
     */
    public static array $container = [];

    /**
     * 收集类注解
     * @param string $class
     * @param array $annotations
     * @return void
     */
    public static function collectClass(string $class, array $annotations): void
    {
        foreach ($annotations as $annotation => $value) {
            static::$container[$class]['class'][$annotation] = $value;
        }
    }

    /**
     * 收集类属性注解
     * @param string $class
     * @param string $property
     * @param array $annotations
     * @return void
     */
    public static function collectProperty(string $class, string $property, array $annotations): void
    {
        foreach ($annotations as $annotation => $value) {
            static::$container[$class]['property'][$property][$annotation] = $value;
        }
    }

    /**
     * 收集方法注解
     * @param string $class
     * @param string $method
     * @param array $annotations
     * @return void
     */
    public static function collectMethod(string $class, string $method, array $annotations): void
    {
        foreach ($annotations as $annotation => $value) {
            static::$container[$class]['method'][$method][$annotation] = $value;
        }
    }

    /**
     * 收集方法参数注解
     * @param string $class
     * @param string $method
     * @param string $param
     * @param array $annotations
     * @return void
     */
    public static function collectMethodParam(string $class, string $method, string $param, array $annotations): void
    {
        foreach ($annotations as $annotation => $value) {
            static::$container[$class]['methodParam'][$method][$param][$annotation] = $value;
        }
    }

    /**
     * @param string|null $key
     * @return mixed
     */
    public static function get(?string $key = null): mixed
    {
        return $key ? Arr::get(static::$container, $key) : static::$container;
    }

    /**
     * @param string|array $key
     * @return bool
     */
    public static function has(string|array $key): bool
    {
        return Arr::has(static::$container, $key);
    }

    /**
     * @return array
     */
    public static function all(): array
    {
        $result = [];

        foreach (static::$container as $class => $data) {
            foreach ($data['class'] ?? [] as $annotation => $value) {
                $result[] = ['type' => 'class', 'class' => $class, 'annotation' => $annotation, 'value' => $value];
            }

            foreach ($data['method'] ?? [] as $method => $items) {
                foreach ($items as $annotation => $value) {
                    $result[] = ['type' => 'method', 'class' => $class, 'method' => $method, 'annotation' => $annotation, 'value' => $value];
                }
            }

            foreach ($data['methodParam'] ?? [] as $method => $params) {
                foreach ($params as $param => $items) {
                    foreach ($items as $annotation => $value) {
                        $result[] = ['type' => 'method_param', 'class' => $class, 'method' => $method, 'param' => $param, 'annotation' => $annotation, 'value' => $value];
                    }
                }
            }

            foreach ($data['property'] ?? [] as $property => $items) {
                foreach ($items as $annotation => $value) {
                    $result[] = ['type' => 'property', 'class' => $class, 'property' => $property, 'annotation' => $annotation, 'value' => $value];
                }
            }
        }

        return $result;
    }

    /**
     * @param string|null $key
     * @return void
     */
    public static function clear(?string $key = null): void
    {
        if ($key) {
            unset(static::$container[$key]);
        } else {
            static::$container = [];
        }
    }

    /**
     * @return array
     */
    public static function getContainer(): array
    {
        return static::$container;
    }
}