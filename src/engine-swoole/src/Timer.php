<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole;

use Larmias\Contracts\TimerInterface;
use Swoole\Coroutine\System;
use Swoole\Timer as SwooleTimer;

class Timer implements TimerInterface
{
    /**
     * @var int[]
     */
    protected array $timeIds = [];

    /**
     * 毫秒定时器间隔触发
     * @param int $duration
     * @param callable $func
     * @param array $args
     * @return int
     */
    public function tick(int $duration, callable $func, array $args = []): int
    {
        $timerId = SwooleTimer::tick($duration, $func, $args);
        $this->timeIds[] = $timerId;
        return $timerId;
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
        $timerId = SwooleTimer::after($duration, $func, $args);
        $this->timeIds[] = $timerId;
        return $timerId;
    }
    
    /**
     * @param float $seconds
     * @return void
     */
    public function sleep(float $seconds): void
    {
        System::sleep($seconds);
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
        foreach ($this->timeIds as $timeId) {
            $this->del($timeId);
        }
        return true;
    }
}