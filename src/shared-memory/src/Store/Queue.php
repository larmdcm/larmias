<?php

declare(strict_types=1);

namespace Larmias\SharedMemory\Store;

use Larmias\SharedMemory\Contracts\QueueInterface;
use SplQueue;

class Queue implements QueueInterface
{
    /**
     * @var SplQueue
     */
    protected SplQueue $queue;

    /**
     * @var SplQueue[]
     */
    protected array $map = [];

    /**
     * @param string $key
     * @param string $data
     * @return bool
     */
    public function enqueue(string $key, string $data): bool
    {
        $this->getQueue($key)->enqueue($data);

        return true;
    }

    /**
     * @param string $key
     * @return string|null
     */
    public function dequeue(string $key): ?string
    {
        $queue = $this->getQueue($key);
        if ($queue->isEmpty()) {
            return null;
        }
        return $queue->dequeue();
    }

    /**
     * @param string $key
     * @return bool
     */
    public function isEmpty(string $key): bool
    {
        return $this->getQueue($key)->isEmpty();
    }

    /**
     * @param string $key
     * @return int
     */
    public function count(string $key): int
    {
        return $this->getQueue($key)->count();
    }

    /**
     * @param string $key
     * @return SplQueue
     */
    protected function getQueue(string $key): SplQueue
    {
        if (!isset($this->map[$key])) {
            $this->map[$key] = new SplQueue();
        }

        return $this->map[$key];
    }
}