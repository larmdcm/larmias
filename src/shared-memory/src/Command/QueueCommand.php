<?php

declare(strict_types=1);

namespace Larmias\SharedMemory\Command;

use Larmias\Contracts\Tcp\ConnectionInterface;
use Larmias\SharedMemory\ConnectionManager;
use Larmias\SharedMemory\Contracts\QueueInterface;
use Larmias\SharedMemory\Message\Result;
use Larmias\SharedMemory\StoreManager;
use SplQueue;

class QueueCommand extends Command
{
    /**
     * @var QueueInterface
     */
    protected QueueInterface $queue;

    /**
     * @return void
     * @throws \Throwable
     */
    public function initialize(): void
    {
        $this->queue = StoreManager::queue();
    }

    /**
     * @param string $key
     * @param string $data
     * @return bool
     */
    public function enqueue(string $key, string $data): bool
    {
        $res = $this->queue->enqueue($key, $data);
        $failQueue = new SplQueue();
        while ($this->queue->hasConsumer($key) && !$this->queue->isEmpty($key)) {
            $item = $this->queue->dequeue($key);
            $consumer = $this->queue->dispatchConsumer($key);
            $connection = $consumer ? ConnectionManager::get($consumer) : null;
            if ($connection) {
                $connection->send(Result::build([
                    'type' => 'consume',
                    'queue' => $key,
                    'data' => $item,
                ]));
            } else {
                $failQueue->enqueue($item);
            }
        }

        while (!$failQueue->isEmpty()) {
            $this->queue->enqueue($key, $failQueue->dequeue());
        }

        return $res;
    }

    /**
     * 添加消费者
     * @param string $key
     * @return array
     * @throws \Throwable
     */
    public function addConsumer(string $key): array
    {
        return [
            'type' => __FUNCTION__,
            'data' => $this->queue->addConsumer($key, $this->getConnection()->getId()),
        ];
    }

    /**
     * 删除消费者
     * @param string $key
     * @return array
     * @throws \Throwable
     */
    public function delConsumer(string $key): array
    {
        return [
            'type' => __FUNCTION__,
            'data' => $this->queue->delConsumer($key, $this->getConnection()->getId()),
        ];
    }

    /**
     * 消费者是否存在
     * @param string $key
     * @return array
     * @throws \Throwable
     */
    public function hasConsumer(string $key): array
    {
        return [
            'type' => __FUNCTION__,
            'data' => $this->queue->hasConsumer($key, $this->getConnection()->getId()),
        ];
    }

    /**
     * @param string $name
     * @param array $args
     * @return mixed
     * @throws \Throwable
     */
    public function __call(string $name, array $args): mixed
    {
        return call_user_func_array([$this->queue, $name], $args);
    }

    /**
     * @param ConnectionInterface $connection
     * @return void
     * @throws \Throwable
     */
    public static function onClose(ConnectionInterface $connection): void
    {
        StoreManager::queue()->unWatch($connection->getId());
    }
}