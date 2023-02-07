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
     * @param int $id
     * @param string $name
     * @return bool
     */
    public function subscribe(int $id, string $name): bool
    {
        $this->clients[$id] = ['name' => $name, 'id' => $id];
        return true;
    }

    /**
     * @param int $id
     * @param string|null $key
     * @return mixed
     */
    public function getInfo(int $id, ?string $key = null): mixed
    {
        $info = $this->clients[$id];
        return $key ? ($info[$key] ?? null) : $info;
    }

    /**
     * @param int $id
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function setInfo(int $id, string $key, mixed $value): bool
    {
        $this->clients[$id][$key] = $value;
        return true;
    }

    /**
     * @return array
     */
    public function online(): array
    {
        return $this->clients;
    }

    /**
     * @param int $id
     * @return int
     */
    public function leave(int $id): int
    {
        if (isset($this->clients[$id])) {
            unset($this->clients[$id]);
            return $id;
        }
        return -1;
    }
}