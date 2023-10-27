<?php

declare(strict_types=1);

namespace Larmias\Engine\WorkerMan\Scheduler;

use Larmias\Engine\WorkerMan\Contracts\SchedulerInterface;
use Larmias\Engine\WorkerMan\Contracts\WorkerInterface;

class WorkerScheduler implements SchedulerInterface
{
    /**
     * @var WorkerInterface[]
     */
    protected array $workers = [];

    /**
     * @param WorkerInterface $worker
     * @return SchedulerInterface
     */
    public function addWorker(WorkerInterface $worker): SchedulerInterface
    {
        $this->workers[] = $worker;
        return $this;
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function start(): void
    {
        foreach ($this->workers as $worker) {
            $worker->process();
        }
    }
}