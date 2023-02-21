<?php

declare(strict_types=1);

namespace Larmias\AsyncQueue\Process;

use Larmias\AsyncQueue\Contracts\QueueInterface;
use Larmias\Engine\Timer;

class ConsumerProcess
{
    /**
     * @param QueueInterface $queue
     */
    public function __construct(protected QueueInterface $queue)
    {
    }

    /**
     * @return void
     */
    public function handle(): void
    {
        Timer::tick(1, function () {
            $this->queue->driver()->consumer();
        });
    }
}