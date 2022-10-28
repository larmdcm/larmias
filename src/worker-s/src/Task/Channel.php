<?php

declare(strict_types=1);

namespace Larmias\WorkerS\Task;

abstract class Channel
{
    /**
     * @var TaskWorker
     */
    protected TaskWorker $taskWorker;

    /**
     * @param TaskWorker  $taskWorker
     * @param array       $config
     * @return Channel
     */
    public static function create(TaskWorker $taskWorker,array $config): Channel
    {
        $channel  = $config['default'] ?? 'file';
        $class    = strpos($channel,"\\") === false ? "\Larmias\WorkerS\Task\Channels\\" . ucfirst($channel) : $channel;
        $channelConfig = $config['channels'][$channel] ?? [];
        /** @var static */
        $instance = new $class($channelConfig);
        $instance->setTaskWorker($taskWorker);
        $instance->init();
        return $instance;
    }

    /**
     * @param TaskWorker $taskWorker
     * @return self
     */
    public function setTaskWorker(TaskWorker $taskWorker): self
    {
        $this->taskWorker = $taskWorker;
        return $this;
    }

    /**
     * @return void
     */
    abstract public function init(): void;

    /**
     * @param  string $raw
     * @return int|null
     */
    abstract public function push(string $raw): ?int;

    /**
     * @return string|null
     */
    abstract public function shift(): ?string;

    /**
     * @return boolean
     */
    abstract public function clear(): bool;

    /**
     * @return boolean
     */
    abstract public function close(): bool;
}