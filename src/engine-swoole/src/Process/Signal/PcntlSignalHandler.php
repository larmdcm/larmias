<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole\Process\Signal;

use Larmias\Contracts\SignalHandlerInterface;

class PcntlSignalHandler implements SignalHandlerInterface
{
    /**
     * @var bool
     */
    public bool $restartSysCall = false;

    /**
     * PcntlSignalHandler __construct
     */
    public function __construct()
    {
        $this->openAsyncSignal();
    }

    /**
     * 开启异步信号
     * @return void
     */
    protected function openAsyncSignal(): void
    {
        pcntl_async_signals(true);
    }

    /**
     * @param int $signal
     * @param callable $func
     * @return bool
     */
    public function onSignal(int $signal, callable $func): bool
    {
        return pcntl_signal($signal, $func, $this->restartSysCall);
    }

    /**
     * @param int $signal
     * @return bool
     */
    public function offSignal(int $signal): bool
    {
        return pcntl_signal($signal, SIG_IGN);
    }
}