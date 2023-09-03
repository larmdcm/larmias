<?php

declare(strict_types=1);

namespace Larmias\Engine;

use ArrayObject;
use Larmias\Contracts\Coroutine\CoroutineCallableInterface;
use RuntimeException;
use function call_user_func_array;
use function call_user_func;

/**
 * @method static int pid(?int $id = null)
 * @method static void set(array $config)
 * @method static ArrayObject|null getContextFor(?int $id = null)
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
     * @param callable $callable
     * @param ...$params
     * @return CoroutineCallableInterface
     */
    public static function create(callable $callable, ...$params): CoroutineCallableInterface
    {
        if (static::isSupport()) {
            return call_user_func([static::$coClass, __FUNCTION__], $callable, ...$params);
        }

        $callable(...$params);

        return new class() implements CoroutineCallableInterface {

            public function execute(callable $callable, ...$params): CoroutineCallableInterface
            {
                return $this;
            }

            public function getId(): int
            {
                return -1;
            }
        };
    }

    /**
     * @return int
     */
    public static function id(): int
    {
        return static::isSupport() ? call_user_func([static::$coClass, __FUNCTION__]) : 0;
    }

    /**
     * @param callable $callable
     * @return void
     */
    public static function defer(callable $callable): void
    {
        static::isSupport() ? call_user_func([static::$coClass, __FUNCTION__], $callable) : $callable();
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
            throw new RuntimeException("not support: Coroutine");
        }

        return call_user_func_array([static::$coClass, $name], $arguments);
    }
}