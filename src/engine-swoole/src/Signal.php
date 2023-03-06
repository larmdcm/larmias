<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole;

use Larmias\Contracts\SignalInterface;
use Swoole\Process;

class Signal implements SignalInterface
{
    /**
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
     * @param int $signal
     * @return bool
     */
    public function offSignal(int $signal): bool
    {
        Process::signal($signal, fn() => null);
        return true;
    }
}