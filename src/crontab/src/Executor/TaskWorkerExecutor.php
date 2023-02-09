<?php

declare(strict_types=1);

namespace Larmias\Crontab\Executor;

use Larmias\Crontab\Crontab;
use Larmias\Crontab\Executor;
use Larmias\Contracts\TaskExecutorInterface;

class TaskWorkerExecutor extends Executor
{
    /**
     * @var TaskExecutorInterface
     */
    protected TaskExecutorInterface $taskExecutor;

    /**
     * @param TaskExecutorInterface $taskExecutor
     * @return void
     */
    public function initialize(TaskExecutorInterface $taskExecutor): void
    {
        $this->taskExecutor = $taskExecutor;
    }

    /**
     * @param Crontab $crontab
     * @return void
     */
    public function execute(Crontab $crontab): void
    {
        $this->taskExecutor->execute([static::class, 'runTask'], [
            'data' => $crontab,
        ]);
    }

    /**
     * @param array $data
     * @return void
     */
    public function runTask(array $data): void
    {
        $this->handle(Crontab::parse($data));
    }
}