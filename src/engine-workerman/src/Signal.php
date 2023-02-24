<?php

declare(strict_types=1);

namespace Larmias\Engine\WorkerMan;

use Larmias\Contracts\SignalInterface;
use Workerman\Events\EventInterface;

class Signal implements SignalInterface
{
    /**
     * @param int $signal
     * @param callable $func
     * @return bool
     */
    public function onSignal(int $signal, callable $func): bool
    {
        Worker::getEventLoop()->add($signal, EventInterface::EV_SIGNAL, $func);
        return true;
    }

    /**
     * @param int $signal
     * @return bool
     */
    public function offSignal(int $signal): bool
    {
        Worker::getEventLoop()->del($signal, EventInterface::EV_SIGNAL);
        return true;
    }
}