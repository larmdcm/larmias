<?php

declare(strict_types=1);

namespace Larmias\Contracts\Coroutine;

interface CoroutineFactoryInterface
{
    /**
     * 创建协程并执行
     * @param callable $callable
     * @param ...$params
     * @return CoroutineCallableInterface
     */
    public function create(callable $callable, ...$params): CoroutineCallableInterface;

    /**
     * 是否支持协程
     * @return bool
     */
    public function isSupport(): bool;
}