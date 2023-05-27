<?php

declare(strict_types=1);

namespace Larmias\AsyncQueue\Process;

use Larmias\AsyncQueue\Contracts\QueueInterface;
use Larmias\Contracts\TimerInterface;

class ConsumerProcess
{
    /**
     * @param QueueInterface $queue
     * @param TimerInterface $timer
     */
    public function __construct(protected QueueInterface $queue, protected TimerInterface $timer)
    {
    }

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->timer->tick(1, function () {
            $this->queue->driver()->consumer();
        });
    }
}