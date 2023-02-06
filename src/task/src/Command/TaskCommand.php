<?php

declare(strict_types=1);

namespace Larmias\Task\Command;

use Larmias\Engine\Contracts\WorkerInterface;
use Larmias\SharedMemory\Command\Command;
use Larmias\Task\Contracts\TaskStoreInterface;
use Larmias\Task\StoreManager;

class TaskCommand extends Command
{
    /**
     * @var string
     */
    public const COMMAND_NAME = 'task';

    /**
     * @var TaskStoreInterface
     */
    protected TaskStoreInterface $taskStore;

    /**
     * @return void
     */
    public function initialize(): void
    {
        $this->taskStore = StoreManager::task();
    }

    /**
     * @param WorkerInterface $worker
     * @return void
     */
    public static function onTick(WorkerInterface $worker): void
    {
    }
}