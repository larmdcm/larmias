<?php

declare(strict_types=1);

namespace Larmias\Timer\Drivers;

use Swoole\Timer;

class Swoole extends Driver
{
    public function tick(int $duration, callable $func, array $args = []): int
    {
        return Timer::tick($duration, $func, $args);
    }

    public function after(int $duration, callable $func, array $args = []): int
    {
        return Timer::after($duration, $func, $args);
    }

    public function del(int $timerId): bool
    {
        return Timer::clear($timerId);
    }

    public function clear(): bool
    {
        return Timer::clearAll();
    }
}