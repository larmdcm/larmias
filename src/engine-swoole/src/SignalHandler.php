<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole;

use Larmias\Contracts\SignalHandlerInterface;
use Swoole\Process;

class SignalHandler implements SignalHandlerInterface
{
    /**
     * 监听信号
     * @param int $signal
     * @param callable $func
     * @return bool
     */
    public function onSignal(int $signal, callable $func): bool
    {
        Process::signal($signal, $func);
        return true;
    }

    /**
     * 移除监听信号
     * @param int $signal
     * @return bool
     */
    public function offSignal(int $signal): bool
    {
        Process::signal($signal, fn() => null);
        return true;
    }
}