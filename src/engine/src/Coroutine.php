<?php

declare(strict_types=1);

namespace Larmias\Engine;

use Larmias\Contracts\CoroutineInterface;
use function call_user_func_array;

/**
 * @method static CoroutineInterface create(callable $callable, ...$params)
 * @method static int pid(?int $id = null)
 * @method static void set(array $config)
 * @method static void defer(callable $callable)
 * @method static \ArrayObject|null getContextFor(?int $id = null)
 */
class Coroutine
{
    /**
     * @var string|null
     */
    protected static ?string $coClass = null;

    /**
     * @param string|null $coClass
     * @return void
     */
    public static function init(?string $coClass = null): void
    {
        static::$coClass = $coClass;
    }

    /**
     * @return int
     */
    public static function id(): int
    {
        return static::isSupport() ? \call_user_func([static::$coClass, __FUNCTION__]) : 0;
    }

    /**
     * @return bool
     */
    public static function isSupport(): bool
    {
        return static::$coClass !== null;
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public static function __callStatic(string $name, array $arguments): mixed
    {
        if (!static::isSupport()) {
            throw new \RuntimeException("not support: Coroutine");
        }

        return call_user_func_array([static::$coClass, $name], $arguments);
    }
}