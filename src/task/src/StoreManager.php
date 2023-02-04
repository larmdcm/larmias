<?php

declare(strict_types=1);

namespace Larmias\Task;

use Larmias\SharedMemory\StoreManager as BaeStoreManager;
use Larmias\Task\Contracts\TaskStoreInterface;
use Larmias\Task\Store\TaskStore;

class StoreManager extends BaeStoreManager
{
    public static function task()
    {
        return StoreManager::getStore(__FUNCTION__, function () {
            if (!isset(static::$container[TaskStoreInterface::class])) {
                static::$container[TaskStoreInterface::class] = TaskStore::class;
            }
            return new static::$container[TaskStoreInterface::class];
        });
    }
}