<?php

declare(strict_types=1);

namespace Larmias\Engine\WorkerMan\Contracts;

interface SchedulerInterface
{
    /**
     * @param WorkerInterface $worker
     * @return SchedulerInterface
     */
    public function addWorker(WorkerInterface $worker): SchedulerInterface;

    /**
     * @return void
     */
    public function start(): void;
}