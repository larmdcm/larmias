<?php

declare(strict_types=1);

namespace Larmias\Task;

use Larmias\SharedMemory\StoreManager as BaeStoreManager;
use Larmias\Task\Contracts\TaskStoreInterface;
use Larmias\Task\Store\TaskStore;

class StoreManager extends BaeStoreManager
{
    /**
     * @return TaskStoreInterface
     * @throws \Throwable
     */
    public static function task(): TaskStoreInterface
    {
        return StoreManager::getStore(__FUNCTION__, function () {
            if (!isset(static::$container[TaskStoreInterface::class])) {
                static::$container[TaskStoreInterface::class] = TaskStore::class;
            }
            return new static::$container[TaskStoreInterface::class];
        });
    }

    /**
     * @return array
     */
    public static function tasks(): array
    {
        return StoreManager::getStores('task');
    }
}