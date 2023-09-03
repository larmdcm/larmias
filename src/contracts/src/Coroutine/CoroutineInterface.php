<?php

declare(strict_types=1);

namespace Larmias\Contracts\Coroutine;

interface CoroutineInterface
{
    /**
     * 协程结束执行
     * @param callable $callable
     * @return void
     */
    public function defer(callable $callable): void;

    /**
     * 是否支持协程模式
     * @return bool
     */
    public function isSupport(): bool;
}