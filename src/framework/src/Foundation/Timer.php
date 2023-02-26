<?php

declare(strict_types=1);

namespace Larmias\Framework\Foundation;

use Larmias\Engine\Timer as EngineTimer;

/**
 * @method static int tick(float $duration, callable $func, array $args = [])
 * @method static int after(float $duration, callable $func, array $args = [])
 * @method static bool del(int $timerId)
 * @method static bool clear()
 */
class Timer
{
    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public static function __callStatic(string $name, array $arguments)
    {
        $timer = EngineTimer::isInit() ? EngineTimer::getTimer() : \Larmias\Timer\Timer::getInstance();
        return \call_user_func_array([$timer, $name], $arguments);
    }
}