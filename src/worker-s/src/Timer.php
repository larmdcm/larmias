<?php

declare(strict_types=1);

namespace Larmias\WorkerS;

use Larmias\WorkerS\Process\Contracts\TimerInterface;
use Larmias\WorkerS\Process\Timer as ProcessTimer;

class Timer
{
    /**
     * @var TimerInterface|null
     */
    protected static ?TimerInterface $timer = null;

    /**
     * 初始化
     *
     * @param TimerInterface|null $timer
     * @return void
     */
    public static function init(?TimerInterface $timer = null)
    {
        if ($timer !== null) {
            static::$timer = $timer;
        }
    }

    /**
     * 定时器间隔触发
     *
     * @param float $time
     * @param callable $func
     * @param array $args
     * @return int
     */
    public static function tick(float $time,callable $func,array $args = []): int
    {
        return static::getTimer()->tick($time,$func,$args);
    }

    /**
     * 定时器延时触发 只会触发一次
     *
     * @param float $time
     * @param callable $func
     * @param array $args
     * @return int
     */
    public static function after(float $time,callable $func,array $args = [])
    {
        return static::getTimer()->after($time,$func,$args);
    }

    /**
     * 清空指定定时器
     *
     * @param int $timerId
     * @return bool
     */
    public static function clear(int $timerId): bool
    {
        return static::getTimer()->clearTimer($timerId);
    }

    /**
     * 清空全部定时器
     *
     * @return bool
     */
    public static function clearAll(): bool
    {
        return static::getTimer()->clearAllTimer();
    }

    /**
     * @return TimerInterface
     */
    protected static function getTimer(): TimerInterface
    {
        if (is_null(static::$timer)) {
            return ProcessTimer::getInstance();
        }
        return static::$timer;
    }
}