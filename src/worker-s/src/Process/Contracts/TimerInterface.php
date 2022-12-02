<?php

declare(strict_types=1);

namespace Larmias\WorkerS\Process\Contracts;

interface TimerInterface
{
    /**
     * 定时器间隔触发
     *
     * @param float     $time
     * @param callable  $func
     * @param array     $args
     * @return integer
     */
    public function tick(float $time,callable $func,array $args = []): int;

    /**
     * 定时器延时触发 只会触发一次
     *
     * @param float    $time
     * @param callable $func
     * @param array    $args
     * @return integer
     */
    public function after(float $time,callable $func,array $args = []): int;

    /**
     * 清空指定定时器
     *
     * @param int $timerId
     * @return boolean
     */
    public function clearTimer(int $timerId): bool;

    /**
     * 清空全部定时器
     *
     * @return boolean
     */
    public function clearAllTimer(): bool;
}