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
     * @var array
     */
    protected array $consumer = [];

    /**
     * @var array
     */
    protected array $watcher = [];

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
     * @param int $id
     * @param string $key
     * @return bool
     */
    public function addWatch(int $id, string $key): bool
    {
        if ($this->hasWatch($id, $key)) {
            return false;
        }

        $this->watcher[$id][$key] = 1;

        return true;
    }

    /**
     * @param int $id
     * @param string|null $key
     * @return bool
     */
    public function hasWatch(int $id, ?string $key = null): bool
    {
        if ($key === null) {
            return !empty($this->watcher[$id]);
        }

        return isset($this->watcher[$id][$key]);
    }

    /**
     * 删除监听
     * @param int $id
     * @return bool
     */
    public function unWatch(int $id): bool
    {
        if (!$this->hasWatch($id)) {
            return false;
        }

        $keys = array_keys($this->watcher[$id]);
        foreach ($keys as $key) {
            $this->delConsumer($key, $id);
        }

        unset($this->watcher[$id]);

        return true;
    }

    /**
     * 添加消费者
     * @param string $key
     * @param int $id
     * @return bool
     */
    public function addConsumer(string $key, int $id): bool
    {
        if ($this->hasConsumer($key, $id)) {
            return true;
        }

        $this->consumer[$key][$id] = 1;
        $this->addWatch($id, $key);

        return true;
    }

    /**
     * 删除消费者
     * @param string $key
     * @param int|null $id
     * @return bool
     */
    public function delConsumer(string $key, ?int $id = null): bool
    {
        if (!$this->hasConsumer($key, $id)) {
            return false;
        }

        if ($id === null) {
            unset($this->consumer[$key]);
            foreach ($this->watcher as $wK => $wItem) {
                $keys = array_keys($wItem);
                foreach ($keys as $k) {
                    if ($k == $key) {
                        unset($this->watcher[$wK][$k]);
                    }
                }
            }

        } else {
            unset($this->consumer[$key][$id]);
            unset($this->watcher[$id][$key]);
        }

        return true;
    }

    /**
     * 是否存在该消费者
     * @param string $key
     * @param int|null $id
     * @return bool
     */
    public function hasConsumer(string $key, ?int $id = null): bool
    {
        if (empty($this->consumer[$key])) {
            return false;
        }

        return $id === null || isset($this->consumer[$key][$id]);
    }

    /**
     * 消费者调度
     * @param string $key
     * @return int|false
     */
    public function dispatchConsumer(string $key): int|false
    {
        if (empty($this->consumer)) {
            return false;
        }

        $k = key($this->consumer[$key]);
        $id = $this->consumer[$key][$k];
        unset($this->consumer[$key][$k]);
        $this->addConsumer($key, $id);
        return $id;
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