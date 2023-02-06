<?php

declare(strict_types=1);

namespace Larmias\Task\Store;

use Larmias\Task\Contracts\TaskStoreInterface;
use Larmias\Task\Task;
use SplPriorityQueue;

class TaskStore implements TaskStoreInterface
{
    /**
     * @var SplPriorityQueue
     */
    protected SplPriorityQueue $scheduler;

    /**
     * @var array
     */
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
    public function add(Task $task): bool
    {
        return $this->scheduler->insert($task, $task->getPriority());
    }

    /**
     * @return Task|null
     */
    public function pop(): ?Task
    {
        return $this->scheduler->isEmpty() ? null : $this->scheduler->extract();
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return $this->scheduler->count();
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->scheduler->isEmpty();
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

    public function online()
    {
    }

    /**
     * @param string $name
     * @return int
     */
    public function leave(string $name): int
    {
        $id = -1;
        if (isset($this->clients[$name])) {
            $id = $this->clients[$name];
            unset($this->clients[$name]);
        }
        return $id;
    }
}