<?php

declare(strict_types=1);

namespace Larmias\Di;

use Larmias\Contracts\Annotation\AnnotationInterface;
use Larmias\Di\Annotation\Inject;
use Larmias\Di\Annotation\InvokeResolver;
use Larmias\Di\AnnotationHandler\InjectAnnotationHandler;
use Larmias\Di\AnnotationHandler\InvokeResolverAnnotationHandler;
use RuntimeException;

class AnnotationManager
{
    /**
     * @var Annotation[]
     */
    protected static array $container = [];

    /**
     * @param AnnotationInterface $annotation
     * @param string $name
     * @return void
     */
    public static function init(AnnotationInterface $annotation, string $name = 'default'): void
    {
        static::$container[$name] = $annotation;
        static::registerHandler(static::$container[$name]);
    }

    /**
     * @param string|array $annotations
     * @param string $handler
     * @return void
     */
    public static function addHandler(string|array $annotations, string $handler): void
    {
        foreach (static::$container as $annotation) {
            $annotation->addHandler($annotations, $handler);
        }
    }

    /**
     * 添加扫描路径
     * @param string|array $path
     * @return void
     */
    public static function addIncludePath(string|array $path): void
    {
        foreach (static::$container as $annotation) {
            $annotation->addIncludePath($path);
        }
    }

    /**
     * 添加扫描排除路径
     * @param string|array $path
     * @return void
     */
    public static function addExcludePath(string|array $path): void
    {
        foreach (static::$container as $annotation) {
            $annotation->addExcludePath($path);
        }
    }

    /**
     * @throws \ReflectionException
     */
    public static function scan(): void
    {
        foreach (static::$container as $annotation) {
            $annotation->scan();
        }
    }

    /**
     * @param string $name
     * @return AnnotationInterface
     */
    public static function get(string $name = 'default'): AnnotationInterface
    {
        if (!static::has($name)) {
            throw new RuntimeException($name . ' not in container');
        }

        return static::$container[$name];
    }

    /**
     * @param string $name
     * @return bool
     */
    public static function has(string $name = 'default'): bool
    {
        return isset(static::$container[$name]);
    }

    /**
     * @param AnnotationInterface $annotation
     * @return void
     */
    public static function registerHandler(AnnotationInterface $annotation): void
    {
        $annotation->addHandler(Inject::class, InjectAnnotationHandler::class);
        $annotation->addHandler(InvokeResolver::class, InvokeResolverAnnotationHandler::class);
    }
}