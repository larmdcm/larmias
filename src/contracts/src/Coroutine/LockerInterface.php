<?php

declare(strict_types=1);

namespace Larmias\Contracts\Coroutine;

interface LockerInterface
{
    /**
     * 加锁
     * @param string|null $key
     * @return bool
     */
    public function lock(?string $key = null): bool;

    /**
     * 解锁
     * @param string|null $key
     * @return bool
     */
    public function unlock(?string $key = null): bool;
}