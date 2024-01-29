<?php

declare(strict_types=1);

namespace Larmias\Di\Invoker;

use Closure;
use Larmias\Di\AnnotationCollector;
use Larmias\Pipeline\Pipeline;
use function is_string;
use function get_class;
use function property_exists;
use function str_contains;
use function explode;
use function array_map;
use function call_user_func;

class InvokeResolver
{
    /**
     * @var array
     */
    protected static array $collect = [];

    /**
     * @param string|object $handler
     * @return void
     */
    public static function add(string|object $handler): void
    {
        $object = is_string($handler) ? new $handler : $handler;
        static::$collect[get_class($object)] = $object;
    }

    /**
     * @return bool
     */
    public static function isEmpty(): bool
    {
        return empty(static::$collect);
    }

    /**
     * @param Closure $process
     * @param array $args
     * @return mixed
     * @throws \Throwable
     */
    public static function process(Closure $process, array $args): mixed
    {
        $pipes = [];
        foreach (static::$collect as $handlerClass => $handler) {
            $classes = property_exists($handler, 'classes') ? $handler->classes : [];
            $annotations = property_exists($handler, 'annotations') ? $handler->annotations : [];
            if (isset($pipes[$handlerClass])) {
                continue;
            }
            foreach ($classes as $classItem) {
                if (str_contains($classItem, '::')) {
                    [$class, $method] = explode('::', $classItem);
                } else {
                    [$class, $method] = [$classItem, '*'];
                }
                if ($class !== $args['class'] || $method !== '*' && $method === $args['method']) {
                    continue;
                }
                $pipes[$handlerClass] = $handler;
                break;
            }
            if (isset($pipes[$handlerClass])) {
                continue;
            }

            foreach ($annotations as $annotation) {
                if (str_contains($annotation, '::')) {
                    [$annotClass, $method] = explode('::', $annotation);
                } else {
                    [$annotClass, $method] = [$annotation, '*'];
                }

                if ($method == '*') {
                    $check = AnnotationCollector::has(implode('.', [
                        $args['class'], 'class', $annotClass
                    ]));
                } else {
                    $check = AnnotationCollector::has(implode('.', [
                        $args['class'], 'method', $method, $annotClass
                    ]));
                }

                if ($check) {
                    $pipes[$handlerClass] = $handler;
                    break;
                }
            }
        }

        if (empty($pipes)) {
            return $process();
        }

        $pipeline = new Pipeline();
        $pipeline->through(
            array_map(function ($handler) {
                return function ($args, $next) use ($handler) {
                    return call_user_func([$handler, 'process'], $args, $next);
                };
            }, $pipes)
        );
        return $pipeline->send($args)->then(function () use ($process) {
            return $process();
        });
    }
}