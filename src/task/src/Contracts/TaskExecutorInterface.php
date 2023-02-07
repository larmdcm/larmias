<?php

declare(strict_types=1);

namespace Larmias\Task\Contracts;

use Closure;
use Larmias\Task\Task;

interface TaskExecutorInterface
{
    /**
     * @param string|array|Closure $handler
     * @param array $args
     * @return bool
     */
    public function execute(string|array|Closure $handler, array $args = []): bool;

    /**
     * @param Task $task
     * @return bool
     */
    public function task(Task $task): bool;
}