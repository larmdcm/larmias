<?php

declare(strict_types=1);

namespace Larmias\Contracts;

interface LockerInterface
{
    /**
     * 获取锁
     * @return bool
     */
    public function acquire(): bool;

    /**
     * 获取锁 block
     * @param int|null $waitTimeout
     * @return bool
     */
    public function block(?int $waitTimeout = null): bool;

    /**
     * 释放锁
     * @return bool
     */
    public function release(): bool;
}