<?php

declare(strict_types=1);

namespace Larmias\Di\Invoker;

use Closure;
use Larmias\Di\AnnotationCollector;
use Larmias\Pipeline\Pipeline;

class InvokeResolver
{
    /**
     * @var array
     */
    protected static array $collect = [];

    public static function add(string|object $handler): void
    {
        $object = \is_string($handler) ? new $handler : $handler;
        static::$collect[\get_class($object)] = $object;
    }

    public static function process(Closure $process, array $args)
    {
        $pipes = [];
        foreach (static::$collect as $handlerClass => $handler) {
            $classes = \property_exists($handler, 'classes') ? $handler->classes : [];
            $annotations = \property_exists($handler, 'annotations') ? $handler->annotations : [];
            if (isset($pipes[$handlerClass])) {
                continue;
            }
            foreach ($classes as $classItem) {
                if (\str_contains($classItem, '::')) {
                    [$class, $method] = \explode('::', $classItem);
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
                if (AnnotationCollector::has([
                    $args['class'], 'method', $args['method'], $annotation
                ])) {
                    $pipes[$handlerClass] = $handler;
                    break;
                }
            }
        }
        $pipeline = new Pipeline();
        $pipeline->through(
            \array_map(function ($handler) {
                return function ($args, $next) use ($handler) {
                    return \call_user_func([$handler, 'process'], $args, $next);
                };
            }, $pipes)
        );
        return $pipeline->send($args)->then(function () use ($process) {
            return $process();
        });
    }
}