<?php

declare(strict_types=1);

namespace Larmias\AsyncQueue\Process;

use Larmias\AsyncQueue\Contracts\QueueInterface;
use Larmias\Contracts\Worker\OnWorkerHandleInterface;
use Larmias\Contracts\Worker\WorkerInterface;

class ConsumerProcess implements OnWorkerHandleInterface
{
    /**
     * @param QueueInterface $queue
     */
    public function __construct(protected QueueInterface $queue)
    {
    }

    /**
     * @param WorkerInterface $worker
     * @return void
     */
    public function onWorkerHandle(WorkerInterface $worker): void
    {
        $this->queue->driver()->consumer();
    }
}