<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole;

use Larmias\Contracts\TimerInterface;
use Swoole\Timer as SwooleTimer;

class Timer implements TimerInterface
{
    /**
     * 毫秒定时器间隔触发
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
     * 毫秒定时器延时触发
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
     * 删除定时器
     * @param int $timerId
     * @return bool
     */
    public function del(int $timerId): bool
    {
        return SwooleTimer::clear($timerId);
    }

    /**
     * 清空定时器
     * @return bool
     */
    public function clear(): bool
    {
        SwooleTimer::clearAll();
        return true;
    }
}