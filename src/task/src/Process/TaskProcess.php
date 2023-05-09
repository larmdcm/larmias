<?php

declare(strict_types=1);

namespace Larmias\Task\Process;

use Larmias\Contracts\ConfigInterface;
use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\Worker\WorkerInterface;
use Larmias\Task\TaskWorker;

class TaskProcess
{
    /**
     * @var TaskWorker
     */
    protected TaskWorker $taskWorker;

    /**
     * @param ContainerInterface $container
     * @param WorkerInterface $worker
     * @param ConfigInterface $config
     */
    public function __construct(protected ContainerInterface $container, protected WorkerInterface $worker, protected ConfigInterface $config)
    {
        $this->taskWorker = new TaskWorker($this->container, $this->config->get('task', []));
        $this->taskWorker->setName('task_worker.' . $this->worker->getWorkerId());
    }

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->taskWorker->run();
    }
}