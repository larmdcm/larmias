<?php

declare(strict_types=1);

namespace Larmias\Engine\Coroutine;

use Larmias\Contracts\Coroutine\CoroutineInterface;
use Larmias\Engine\Coroutine as Co;

class Coroutine implements CoroutineInterface
{
    /**
     * @param callable $callable
     * @return void
     */
    public function defer(callable $callable): void
    {
        Co::defer($callable);
    }

    /**
     * 是否支持协程
     * @return bool
     */
    public function isSupport(): bool
    {
        return Co::isSupport();
    }
}