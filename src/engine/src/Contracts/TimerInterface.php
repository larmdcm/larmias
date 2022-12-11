<?php

declare(strict_types=1);

namespace Larmias\Engine\Contracts;

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
     * 删除指定定时器
     *
     * @param int $timerId
     * @return boolean
     */
    public function del(int $timerId): bool;

    /**
     * 清空全部定时器
     *
     * @return boolean
     */
    public function clear(): bool;
}