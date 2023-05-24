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

    /**
     * @var array
     */
    protected array $tasks = [];

    public function __construct()
    {
        $this->scheduler = new SplPriorityQueue();
        $this->scheduler->setExtractFlags(SplPriorityQueue::EXTR_DATA);
    }

    /**
     * @param Task $task
     * @param int|null $id
     * @return bool
     */
    public function publish(Task $task, ?int $id = null): bool
    {
        return $this->scheduler->insert(['task' => $task, 'id' => $id], $task->getPriority());
    }

    /**
     * @return array|null
     */
    public function pop(): ?array
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
     * @param string $taskId
     * @param int $id
     * @return bool
     */
    public function taskPush(string $taskId, int $id): bool
    {
        $this->tasks[$taskId] = $id;
        return true;
    }

    /**
     * @param string $taskId
     * @return int|null
     */
    public function taskFinish(string $taskId): ?int
    {
        $id = $this->tasks[$taskId] ?? null;
        if ($id) {
            unset($this->tasks[$taskId]);
        }
        return $id;
    }

    /**
     * @param int $id
     * @return bool
     */
    public function taskClear(int $id): bool
    {
        foreach ($this->tasks as $taskId => $connId) {
            if ($id === $connId) {
                unset($this->tasks[$taskId]);
            }
        }
        return true;
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