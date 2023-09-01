<?php

declare(strict_types=1);

namespace Larmias\Engine;

use ArrayObject;
use RuntimeException;
use Larmias\Contracts\CoroutineInterface;
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
     * @return CoroutineInterface
     */
    public static function create(callable $callable, ...$params): CoroutineInterface
    {
        if (static::isSupport()) {
            return call_user_func([static::$coClass, __FUNCTION__], $callable, ...$params);
        }

        $callable(...$params);
        return new class($callable) implements CoroutineInterface {
            /**
             * @var callable
             */
            protected $callback;

            public function __construct(callable $callback)
            {
                $this->callback = $callback;
            }

            public static function create(callable $callable, ...$params): CoroutineInterface
            {
                throw new RuntimeException('Not supported.');
            }

            public function execute(...$params): CoroutineInterface
            {
                call_user_func_array($this->callback, $params);
                return $this;
            }

            public function getId(): int
            {
                return 0;
            }

            public static function id(): int
            {
                throw new RuntimeException('Not supported.');
            }

            public static function pid(?int $id = null): int
            {
                throw new RuntimeException('Not supported.');
            }

            public static function set(array $config): void
            {
                throw new RuntimeException('Not supported.');
            }

            public static function defer(callable $callable): void
            {
                throw new RuntimeException('Not supported.');
            }

            public static function getContextFor(?int $id = null): ?ArrayObject
            {
                throw new RuntimeException('Not supported.');
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