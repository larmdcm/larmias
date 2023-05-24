<?php

declare(strict_types=1);

namespace Larmias\Timer;

use Larmias\Contracts\TimerInterface;
use Larmias\Timer\Driver\Alarm;
use Larmias\Timer\Driver\Swoole;
use RuntimeException;
use Swoole\Coroutine;
use function extension_loaded;
use function call_user_func_array;

/**
 * @method static int tick(int $duration, callable $func, array $args = [])
 * @method static int after(int $duration, callable $func, array $args = [])
 * @method static bool del(int $timerId)
 * @method static bool clear()
 */
class Timer
{
    /** @var TimerInterface|null */
    protected static ?TimerInterface $timer = null;

    /**
     * @var bool
     */
    protected static bool $isInit = false;

    /**
     * 初始化
     *
     * @param TimerInterface $timer
     * @return void
     */
    public static function init(TimerInterface $timer): void
    {
        static::$timer = $timer;
        static::$isInit = true;
    }

    /**
     * @return bool
     */
    public static function isInit(): bool
    {
        return static::$isInit;
    }

    /**
     * @return TimerInterface
     */
    public static function getTimer(): TimerInterface
    {
        if (!static::$timer) {
            if (extension_loaded('swoole') && Coroutine::getCid() > 0) {
                static::$timer = Swoole::getInstance();
            } else if (extension_loaded('pcntl')) {
                static::$timer = Alarm::getInstance();
            } else {
                throw new RuntimeException('Not Support Timer');
            }
        }

        return static::$timer;
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public static function __callStatic(string $name, array $arguments): mixed
    {
        return call_user_func_array([static::getTimer(), $name], $arguments);
    }
}