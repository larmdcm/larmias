<?php

declare(strict_types=1);

namespace Larmias\Contracts;

interface SignalHandlerInterface
{
    /**
     * 监听信号
     * @param integer $signal
     * @param callable $func
     * @return bool
     */
    public function onSignal(int $signal, callable $func): bool;

    /**
     * 移除监听信号
     * @param integer $signal
     * @return bool
     */
    public function offSignal(int $signal): bool;
}