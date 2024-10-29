<?php

declare(strict_types=1);

namespace Larmias\Di\Aop;

use Closure;
use Larmias\Di\Annotation\Aspect;
use Larmias\Di\AnnotationCollector;

class AspectCollector
{
    /**
     * @var array
     */
    public static array $container = [];

    /**
     * @param array $param
     * @return void
     */
    public static function collect(array $param): void
    {
        if ($param['annotation'] !== Aspect::class) {
            return;
        }

        static::parse($param, function (array $params) {
            ['class' => $class, 'method' => $method, 'aspectHandler' => $aspectHandler] = $params;
            if (isset(static::$container[$class]['*'])) {
                $method = '*';
            }
            static::$container[$class][$method][] = $aspectHandler;
        });
    }

    /**
     * @param array $param
     * @param Closure $closure
     * @return void
     */
    public static function parse(array $param, Closure $closure): void
    {
        static $annotationMap = null;
        if ($annotationMap === null) {
            $annotationMap = self::getAnnotationMap();
        }

        foreach ($param['value'] as $value) {
            $classes = $value->classes;
            $annotations = $value->annotations;
            foreach ($annotations as $annotation) {
                if (str_contains($annotation, '::')) {
                    [$annotClass, $method] = explode('::', $annotation);
                } else {
                    [$annotClass, $method] = [$annotation, '*'];
                }

                $tmpClass = $annotationMap[$annotClass] ?? [];
                if (empty($tmpClass)) {
                    continue;
                }

                foreach ($tmpClass as $className => $classMethods) {
                    if ($method === '*') {
                        $classes[] = $className;
                        continue;
                    }
                    $tmpMethods = $classMethods[$method] ?? [];
                    if (empty($tmpMethods)) {
                        continue;
                    }
                    foreach ($tmpMethods as $m) {
                        $classes[] = $className . '::' . $m;
                    }
                }
            }

            foreach ($classes as $classItem) {
                if (str_contains($classItem, '::')) {
                    [$class, $method] = explode('::', $classItem);
                } else {
                    [$class, $method] = [$classItem, '*'];
                }
                $closure([
                    'class' => $class,
                    'method' => $method,
                    'aspectHandler' => $param['class'],
                ]);
            }
        }
    }

    /**
     * @return array
     */
    public static function getAspectClasses(): array
    {
        return array_keys(self::$container);
    }

    /**
     * @param string $class
     * @param string $method
     * @return array
     * @throws \Throwable
     */
    public static function getMethodAspects(string $class, string $method): array
    {
        $aspects = static::$container[$class][$method] ?? [];
        if (!empty(static::$container[$class]['*'])) {
            $aspects = array_merge($aspects, static::$container[$class]['*']);
        }

        return array_values(array_unique($aspects));
    }

    /**
     * @return array
     */
    public static function getAnnotationMap(): array
    {
        $list = AnnotationCollector::all();
        $result = [];
        foreach ($list as $item) {
            if (!in_array($item['type'], ['class', 'method'])) {
                continue;
            }
            $method = $item['type'] === 'method' ? $item['method'] : '*';
            $result[$item['annotation']][$item['class']][$method][] = $item['value'];
        }
        return $result;
    }
}