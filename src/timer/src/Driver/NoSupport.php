<?php

declare(strict_types=1);

namespace Larmias\Timer\Driver;

class NoSupport extends Driver
{
    public function tick(int $duration, callable $func, array $args = []): int
    {
        return -1;
    }

    public function after(int $duration, callable $func, array $args = []): int
    {
        return -1;
    }

    public function del(int $timerId): bool
    {
        return false;
    }

    public function clear(): bool
    {
        return false;
    }
}