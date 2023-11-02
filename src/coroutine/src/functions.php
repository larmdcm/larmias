<?php

declare(strict_types=1);

namespace Larmias\Coroutine;

use Larmias\Contracts\Coroutine\ChannelInterface;
use Larmias\Coroutine\Concurrent\Parallel;
use Closure;
use Larmias\Coroutine\Sync\Waiter;

/**
 * @param array $callables
 * @param int $concurrent
 * @return array
 * @throws \Throwable
 */
function parallel(array $callables, int $concurrent = 0): array
{
    $parallel = new Parallel($concurrent);
    foreach ($callables as $key => $callable) {
        $parallel->add($callable, $key);
    }
    return $parallel->wait();
}

/**
 * @param Closure $closure
 * @param float|null $timeout
 * @return mixed
 * @throws \Throwable
 */
function wait(Closure $closure, ?float $timeout = null): mixed
{
    return (new Waiter())->wait($closure, $timeout);
}

/**
 * @param callable $callable
 * @return int
 */
function go(callable $callable): int
{
    return Coroutine::create($callable)->getId();
}

/**
 * @param int $size
 * @return ChannelInterface
 */
function channel(int $size = 0): ChannelInterface
{
    return ChannelFactory::make($size);
}

/**
 * @param callable $callable
 * @return void
 */
function defer(callable $callable): void
{
    Coroutine::defer($callable);
}