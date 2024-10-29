<?php

declare(strict_types=1);

namespace Larmias\Coroutine\Sync;

use function Larmias\Coroutine\try_lock;

class Once
{
    protected static array $container = [];

    /**
     * @var Once|null
     */
    protected static ?Once $instance = null;

    /**
     * @return Once
     */
    public static function getInstance(): Once
    {
        if (!static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * @param callable $callable
     * @param string|null $key
     * @return bool
     */
    public function do(callable $callable, ?string $key = null): bool
    {
        if (!$key) {
            $key = static::class;
        }

        if (isset(static::$container[$key])) {
            return false;
        }

        return try_lock(function () use ($key, $callable) {
            if (isset(static::$container[$key])) {
                return false;
            }
            call_user_func($callable);
            return static::$container[$key] = true;
        }, $key);
    }
}