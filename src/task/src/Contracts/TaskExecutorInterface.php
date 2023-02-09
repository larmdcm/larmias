<?php

declare(strict_types=1);

namespace Larmias\Task\Contracts;

use Larmias\Contracts\TaskExecutorInterface as BaseTaskExecutorInterface;
use Larmias\Task\Task;

interface TaskExecutorInterface extends BaseTaskExecutorInterface
{
    public function task(Task $task): bool;
}