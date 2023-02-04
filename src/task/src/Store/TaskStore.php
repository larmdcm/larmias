<?php

declare(strict_types=1);

namespace Larmias\Task\Store;

use Larmias\Task\Contracts\TaskStoreInterface;
use Larmias\Task\Task;
use SplPriorityQueue;

class TaskStore implements TaskStoreInterface
{
    protected SplPriorityQueue $scheduler;

    protected array $clients = [];

    public function __construct()
    {
        $this->scheduler = new SplPriorityQueue();
        $this->scheduler->setExtractFlags(SplPriorityQueue::EXTR_DATA);
    }

    /**
     * @param Task $task
     * @return bool
     */
    public function push(Task $task): bool
    {
        return $this->scheduler->insert($task, $task->getPriority());
    }

    /**
     * @param string $name
     * @param int $id
     * @return bool
     */
    public function join(string $name, int $id): bool
    {
        $this->clients[$name] = $id;
        return true;
    }
}