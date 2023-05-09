<?php

declare(strict_types=1);

namespace Larmias\Task\Contracts;

use Larmias\Contracts\TaskExecutorInterface as BaseTaskExecutorInterface;
use Larmias\Task\Task;

interface TaskExecutorInterface extends BaseTaskExecutorInterface
{
    /**
     * @param Task $task
     * @return bool
     */
    public function task(Task $task): bool;
}