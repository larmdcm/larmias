<?php

declare(strict_types=1);

namespace Larmias\Task;

use Closure;
use Larmias\Contracts\ConfigInterface;
use Larmias\Task\Client\Client;
use Larmias\Task\Contracts\TaskExecutorInterface;

class TaskExecutor implements TaskExecutorInterface
{
    /**
     * @var Client
     */
    protected Client $client;

    /**
     * @param ConfigInterface $config
     */
    public function __construct(protected ConfigInterface $config)
    {
        $this->client = new Client($this->config->get('task', []));
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
     * @param Task $task
     * @return bool
     */
    public function task(Task $task): bool
    {
        return $this->client->publish($task);
    }
}