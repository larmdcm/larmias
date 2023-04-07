<?php

declare(strict_types=1);

namespace Larmias\Framework\Foundation;

use Larmias\Engine\Timer as EngineTimer;
use Larmias\Timer\Timer as ProcessTimer;
use function call_user_func_array;

/**
 * @method static int tick(int $duration, callable $func, array $args = [])
 * @method static int after(int $duration, callable $func, array $args = [])
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

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public static function __callStatic(string $name, array $arguments)
    {
        $timer = EngineTimer::isInit() ? EngineTimer::getTimer() : ProcessTimer::getTimer();
        return call_user_func_array([$timer, $name], $arguments);
    }
}