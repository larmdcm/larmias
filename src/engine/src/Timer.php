<?php

declare(strict_types=1);

namespace Larmias\Engine;

use Larmias\Engine\Contracts\TimerInterface;

/**
 * @method static int tick(float $time,callable $func,array $args = [])
 * @method static int after(float $time,callable $func,array $args = [])
 * @method static bool del(int $timerId)
 * @method static bool clear()
 */
class Timer
{
    /** @var int */
    public const MILLISECOND = 1;
    /** @var int */
    public const SECOND = 1000 * self::MILLISECOND;
    /** @var int */
    public const MINUTE = 60 * self::SECOND;
    /** @var int */
    public const HOUR = 60 * self::MINUTE;

    /** @var TimerInterface */
    protected static TimerInterface $timer;

    /**
     * 初始化
     *
     * @param TimerInterface $timer
     * @return void
     */
    public static function init(TimerInterface $timer): void
    {
        static::$timer = $timer;
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public static function __callStatic(string $name,array $arguments): mixed
    {
        return call_user_func_array([static::$timer,$name],$arguments);
    }
}