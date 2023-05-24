<?php

declare(strict_types=1);

namespace Larmias\Contracts;

interface TimerInterface
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
     * 毫秒定时器间隔触发
     * @param int $duration
     * @param callable $func
     * @param array $args
     * @return int
     */
    public function tick(int $duration, callable $func, array $args = []): int;

    /**
     * 毫秒定时器延时触发 只会触发一次
     * @param int $duration
     * @param callable $func
     * @param array $args
     * @return int
     */
    public function after(int $duration, callable $func, array $args = []): int;

    /**
     * 删除指定定时器
     * @param int $timerId
     * @return boolean
     */
    public function del(int $timerId): bool;

    /**
     * 清空全部定时器
     * @return boolean
     */
    public function clear(): bool;
}