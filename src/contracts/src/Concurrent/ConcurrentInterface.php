<?php

declare(strict_types=1);

namespace Larmias\Contracts\Concurrent;

use Larmias\Contracts\Coroutine\ChannelInterface;

interface ConcurrentInterface
{
    /**
     * @param callable $callable
     * @return void
     */
    public function create(callable $callable): void;

    /**
     * @return int
     */
    public function limit(): int;

    /**
     * @return int
     */
    public function length(): int;

    /**
     * @return ChannelInterface
     */
    public function getChannel(): ChannelInterface;
}