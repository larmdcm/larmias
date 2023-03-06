<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole;

use Larmias\Contracts\TimerInterface;
use Swoole\Timer as SwooleTimer;

class Timer implements TimerInterface
{
    /**
     * @param int $duration
     * @param callable $func
     * @param array $args
     * @return int
     */
    public function tick(int $duration, callable $func, array $args = []): int
    {
        return SwooleTimer::tick($duration, $func, $args);
    }

    /**
     * @param int $duration
     * @param callable $func
     * @param array $args
     * @return int
     */
    public function after(int $duration, callable $func, array $args = []): int
    {
        return SwooleTimer::after($duration, $func, $args);
    }

    /**
     * @param int $timerId
     * @return bool
     */
    public function del(int $timerId): bool
    {
        return SwooleTimer::clear($timerId);
    }

    /**
     * @return bool
     */
    public function clear(): bool
    {
        SwooleTimer::clearAll();
        return true;
    }
}