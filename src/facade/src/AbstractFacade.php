<?php

declare(strict_types=1);

namespace Larmias\Facade;

use Larmias\Contracts\ContainerInterface;
use RuntimeException;
use Closure;
use function call_user_func_array;
use function is_object;
use function class_exists;

abstract class AbstractFacade
{
    /**
     * @var ContainerInterface|null
     */
    public static ?ContainerInterface $container = null;

    /**
     * @var array
     */
    protected static array $instances = [];

    /**
     * create facade instance.
     *
     * @return object
     */
    public static function createFacade(): object
    {
        $facadeAccessor = static::getFacadeAccessor();
        $newInstance = static::alwaysNewInstance();

        if (static::$container) {
            return static::$container->make($facadeAccessor, [], $newInstance);
        }

        if (!$newInstance && isset(static::$instances[static::class])) {
            return static::$instances[static::class];
        }

        if (is_object($facadeAccessor)) {
            $instance = $facadeAccessor;
        } else if ($facadeAccessor instanceof Closure) {
            $instance = $facadeAccessor();
        } else {
            if (!class_exists($facadeAccessor)) {
                throw new RuntimeException($facadeAccessor . ' class not exists.');
            }
            $instance = new $facadeAccessor();
        }
        return static::$instances[static::class] = $instance;
    }

    /**
     * @return string|object
     */
    abstract public static function getFacadeAccessor(): string|object;

    /**
     * @return bool
     */
    public static function alwaysNewInstance(): bool
    {
        return false;
    }

    /**
     * Facade __callStatic.
     *
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public static function __callStatic(string $name, array $arguments)
    {
        return call_user_func_array([static::createFacade(), $name], $arguments);
    }

    /**
     * @return ContainerInterface
     */
    public static function getContainer(): ContainerInterface
    {
        return self::$container;
    }

    /**
     * @param ContainerInterface $container
     */
    public static function setContainer(ContainerInterface $container): void
    {
        self::$container = $container;
    }
}