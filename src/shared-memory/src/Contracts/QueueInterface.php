<?php

declare(strict_types=1);

namespace Larmias\SharedMemory\Contracts;

interface QueueInterface
{
    /**
     * @param string $key
     * @param string $data
     * @return bool
     */
    public function enqueue(string $key, string $data): bool;

    /**
     * @param string $key
     * @return string|null
     */
    public function dequeue(string $key): ?string;

    /**
     * @param string $key
     * @return bool
     */
    public function isEmpty(string $key): bool;

    /**
     * @param string $key
     * @return int
     */
    public function count(string $key): int;

    /**
     * 添加消费者
     * @param string $key
     * @param int $id
     * @return bool
     */
    public function addConsumer(string $key, int $id): bool;

    /**
     * 删除消费者
     * @param string $key
     * @param int|null $id
     * @return bool
     */
    public function delConsumer(string $key, ?int $id = null): bool;

    /**
     * 是否存在该消费者
     * @param string $key
     * @param int|null $id
     * @return bool
     */
    public function hasConsumer(string $key, ?int $id = null): bool;

    /**
     * 消费者调度
     * @param string $key
     * @return int|false
     */
    public function dispatchConsumer(string $key): int|false;

    /**
     * 删除消费者监听
     * @param int $id
     * @return bool
     */
    public function unWatch(int $id): bool;
}