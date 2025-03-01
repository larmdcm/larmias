<?php

declare(strict_types=1);

namespace Larmias\Engine;

use Larmias\Contracts\TimerInterface;
use RuntimeException;
use function call_user_func_array;

/**
 * @method static int tick(int $duration, callable $func, array $args = [])
 * @method static int after(int $duration, callable $func, array $args = [])
 * @method static bool sleep(float $seconds)
 * @method static bool del(int $timerId)
 * @method static bool clear()
 */
class Timer
{
    /** @var TimerInterface|null */
    protected static ?TimerInterface $timer = null;

    /**
     * 初始化
     * @param TimerInterface $timer
     * @return void
     */
    public static function init(TimerInterface $timer): void
    {
        static::$timer = $timer;
    }

    /**
     * @return TimerInterface
     */
    public static function getTimer(): TimerInterface
    {
        return static::$timer;
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public static function __callStatic(string $name, array $arguments): mixed
    {
        if (static::$timer === null) {
            throw new RuntimeException("not support: Timer");
        }

        return call_user_func_array([static::$timer, $name], $arguments);
    }
}