<?php

declare(strict_types=1);

namespace Larmias\Contracts\Coroutine;

use Larmias\Contracts\CoroutineInterface;

interface CoroutineFactoryInterface
{
    /**
     * @param callable $callable
     * @param ...$params
     * @return CoroutineInterface
     */
    public function create(callable $callable, ...$params): CoroutineInterface;

    /**
     * @param callable $callable
     * @return void
     */
    public function defer(callable $callable): void;
}