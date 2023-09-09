<?php

declare(strict_types=1);

namespace Larmias\Engine;

use ArrayObject;
use Larmias\Contracts\Coroutine\CoroutineInterface;
use Larmias\Contracts\Coroutine\CoroutineCallableInterface;
use RuntimeException;
use function call_user_func_array;

/**
 * @method static CoroutineCallableInterface create(callable $callable, ...$params)
 * @method static int id()
 * @method static int pid(?int $id = null)
 * @method static void set(array $config)
 * @method static void defer(callable $callable)
 * @method static ArrayObject|null getContextFor(?int $id = null)
 * @method static void yield()
 * @method static void resume(int $id)
 */
class Coroutine
{
    /**
     * @var CoroutineInterface|null
     */
    protected static ?CoroutineInterface $coroutine = null;

    /**
     * @param CoroutineInterface|null $coroutine
     * @return void
     */
    public static function init(CoroutineInterface $coroutine = null): void
    {
        static::$coroutine = $coroutine;
    }

    /**
     * @return bool
     */
    public static function isSupport(): bool
    {
        return static::$coroutine !== null;
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public static function __callStatic(string $name, array $arguments): mixed
    {
        if (!static::isSupport()) {
            throw new RuntimeException("not support: Coroutine");
        }

        return call_user_func_array([static::$coroutine, $name], $arguments);
    }
}