<?php

declare(strict_types=1);

namespace Larmias\Task;

use Larmias\Contracts\ConfigInterface;
use Larmias\Contracts\ContainerInterface;
use Larmias\Task\Client\Connection;
use Larmias\Task\Contracts\TaskExecutorInterface;
use Closure;
use Throwable;

class TaskExecutor implements TaskExecutorInterface
{
    /**
     * @var Connection
     */
    protected Connection $client;

    /**
     * @param ContainerInterface $container
     * @param ConfigInterface $config
     * @throws Throwable
     */
    public function __construct(protected ContainerInterface $container, protected ConfigInterface $config)
    {
        $this->client = new Connection($this->container, $this->config->get('task', []));
    }

    /**
     * @param string|array|Closure $handler
     * @param array $args
     * @return bool
     */
    public function execute(string|array|Closure $handler, array $args = []): bool
    {
        return $this->task(new Task($handler, $args));
    }

    /**
     * @param string|array|Closure $handler
     * @param array $args
     * @return mixed
     */
    public function syncExecute(string|array|Closure $handler, array $args = []): mixed
    {
        return $this->syncTask(new Task($handler, $args));
    }

    /**
     * @param Task $task
     * @return bool
     */
    public function task(Task $task): bool
    {
        return $this->publishTask($task);
    }

    /**
     * @param Task $task
     * @return mixed
     */
    public function syncTask(Task $task): mixed
    {
        return $this->publishTask($task, false);
    }

    /**
     * @param Task $task
     * @param bool $async
     * @return mixed
     */
    public function publishTask(Task $task, bool $async = true): mixed
    {
        $this->client->publish($task, function () use ($async, $task) {
            if ($async) {
                $this->client->syncWait->done($task->getId(), true);
            }
        });
        return $this->client->syncWait->wait($task->getId());
    }
}