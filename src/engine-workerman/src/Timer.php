<?php

declare(strict_types=1);

namespace Larmias\Engine\WorkerMan;

use Larmias\Contracts\TimerInterface;
use Workerman\Timer as WorkerManTimer;

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
        return WorkerManTimer::add(floatval($duration / 1000), $func, $args);
    }

    /**
     * @param int $duration
     * @param callable $func
     * @param array $args
     * @return int
     */
    public function after(int $duration, callable $func, array $args = []): int
    {
        return WorkerManTimer::add(floatval($duration / 1000), $func, $args, false);
    }

    /**
     * @param int $timerId
     * @return bool
     */
    public function del(int $timerId): bool
    {
        return WorkerManTimer::del($timerId);
    }

    /**
     * @return bool
     */
    public function clear(): bool
    {
        WorkerManTimer::delAll();
        return true;
    }
}