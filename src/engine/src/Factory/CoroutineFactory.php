<?php

declare(strict_types=1);

namespace Larmias\Engine\Factory;

use Larmias\Contracts\Coroutine\CoroutineFactoryInterface;
use Larmias\Contracts\Coroutine\CoroutineCallableInterface;
use Larmias\Engine\Coroutine;

class CoroutineFactory implements CoroutineFactoryInterface
{
    /**
     * 创建协程并执行
     * @param callable $callable
     * @param ...$params
     * @return CoroutineCallableInterface
     */
    public function create(callable $callable, ...$params): CoroutineCallableInterface
    {
        return Coroutine::create($callable, ...$params);
    }

    /**
     * 是否支持协程
     * @return bool
     */
    public function isSupport(): bool
    {
        return Coroutine::isSupport();
    }
}