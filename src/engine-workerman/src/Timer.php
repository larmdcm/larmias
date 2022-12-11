<?php

declare(strict_types=1);

namespace Larmias\Engine\WorkerMan;

use Larmias\Engine\Contracts\TimerInterface;
use Workerman\Timer as WorkerManTimer;

class Timer implements TimerInterface
{
    /**
     * @param float $time
     * @param callable $func
     * @param array $args
     * @return int
     */
    public function tick(float $time, callable $func, array $args = []): int
    {
        return WorkerManTimer::add($time, $func, $args);
    }

    /**
     * @param float $time
     * @param callable $func
     * @param array $args
     * @return int
     */
    public function after(float $time, callable $func, array $args = []): int
    {
        return WorkerManTimer::add($time, $func, $args, false);
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