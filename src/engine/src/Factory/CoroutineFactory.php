<?php

declare(strict_types=1);

namespace Larmias\Engine\Factory;

use Larmias\Contracts\Coroutine\CoroutineFactoryInterface;
use Larmias\Contracts\CoroutineInterface;
use Larmias\Engine\Coroutine;

class CoroutineFactory implements CoroutineFactoryInterface
{
    /**
     * @param callable $callable
     * @param ...$params
     * @return CoroutineInterface
     */
    public function create(callable $callable, ...$params): CoroutineInterface
    {
        return Coroutine::create($callable, ...$params);
    }

    /**
     * @param callable $callable
     * @return void
     */
    public function defer(callable $callable): void
    {
        Coroutine::defer($callable);
    }
}