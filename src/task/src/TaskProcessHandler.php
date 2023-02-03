<?php

declare(strict_types=1);

namespace Larmias\Task;

use Larmias\Engine\Contracts\WorkerInterface;

class TaskProcessHandler
{
    protected TaskWorker $taskWorker;

    public function __construct(protected WorkerInterface $worker)
    {
        $this->taskWorker = new TaskWorker();
    }

    public function handle(): void
    {
    }
}