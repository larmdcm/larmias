<?php

declare(strict_types=1);

namespace Larmias\Engine\WorkerMan;

use Larmias\Contracts\SignalHandlerInterface;

class SignalHandler implements SignalHandlerInterface
{
    /**
     * @param int $signal
     * @param callable $func
     * @return bool
     */
    public function onSignal(int $signal, callable $func): bool
    {
        Worker::getEventLoop()->onSignal($signal, $func);
        return true;
    }

    /**
     * @param int $signal
     * @return bool
     */
    public function offSignal(int $signal): bool
    {
        Worker::getEventLoop()->offSignal($signal);
        return true;
    }
}