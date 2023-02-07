<?php

declare(strict_types=1);

namespace Larmias\Task\Command;

use Larmias\Contracts\Tcp\ConnectionInterface;
use Larmias\Engine\Contracts\WorkerInterface;
use Larmias\SharedMemory\Command\Command;
use Larmias\SharedMemory\ConnectionManager;
use Larmias\SharedMemory\Message\Result;
use Larmias\Task\Contracts\TaskStoreInterface;
use Larmias\Task\Enum\WorkerStatus;
use Larmias\Task\StoreManager;
use Larmias\Task\Task;

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
        $taskStore = StoreManager::task();
        while (!$taskStore->isEmpty()) {
            $isConsume = false;
            foreach ($taskStore->online() as $id => $info) {
                if (isset($info['status']) && $info['status'] === WorkerStatus::IDLE) {
                    $connection = ConnectionManager::get($id);
                    $connection?->send(Result::build([
                        'type' => 'message',
                        'name' => $info['name'] ?? null,
                        'task' => $taskStore->pop()
                    ]));
                    $taskStore->setInfo($id, 'status', WorkerStatus::RUNNING);
                    $isConsume = true;
                }
            }
            if (!$isConsume) {
                break;
            }
        }
    }

    /**
     * @param ConnectionInterface $connection
     * @return void
     */
    public static function onClose(ConnectionInterface $connection): void
    {
        StoreManager::task()->leave($connection->getId());
    }

    /**
     * @return array
     */
    public function publish(): array
    {
        $data = $this->command->args[0];
        $task = Task::parse($data);
        $this->taskStore->add($task);
        return [
            'type' => __FUNCTION__,
            'task_id' => $task->getId(),
        ];
    }

    /**
     * @return array
     */
    public function subscribe(): array
    {
        $id = $this->getConnection()->getId();
        $name = $this->command->args[0];
        $this->taskStore->subscribe($id, $name);
        return [
            'type' => __FUNCTION__,
            'name' => $name,
        ];
    }

    /**
     * @return array
     */
    public function getInfo(): array
    {
        $id = $this->getConnection()->getId();
        $key = $this->command->args[0];
        $value = $this->taskStore->getInfo($id, $key);
        return [
            'type' => __FUNCTION__,
            'key' => $key,
            'value' => $value,
        ];
    }

    /**
     * @return array
     */
    public function setInfo(): array
    {
        $id = $this->getConnection()->getId();
        $key = $this->command->args[0];
        $this->taskStore->setInfo($id, $key, $this->command->args[1]);
        return [
            'type' => __FUNCTION__,
            'key' => $key,
        ];
    }

    /**
     * @return array
     */
    public function online(): array
    {
        return [
            'type' => __FUNCTION__,
            'online' => $this->taskStore->online(),
        ];
    }

    /**
     * @return array
     */
    public function leave(): array
    {
        $id = $this->getConnection()->getId();
        $this->taskStore->leave($id);
        return [
            'type' => __FUNCTION__
        ];
    }
}