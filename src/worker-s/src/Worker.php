<?php

declare(strict_types=1);

namespace Larmias\WorkerS;

use Larmias\WorkerS\Support\Arr;
use Larmias\WorkerS\Task\TaskWorker;
use Larmias\WorkerS\Concerns\HasEvents;

class Worker
{
    use HasEvents;

    /**
     * @var string
     */
    protected string $objectId;

    /**
     * @var string
     */
    protected string $name = 'worker';

    /**
     * @var array
     */
    protected array $config = [];

    /**
     * @var TaskWorker
     */
    protected TaskWorker $taskWorker;

    /**
     * Worker constructor.
     * @param int $workerNum
     */
    public function __construct(int $workerNum = 1)
    {
        $this->objectId = \session_create_id();
        $this->config['worker_num'] = $workerNum;
        Manager::add($this);
    }

    /**
     * 初始化
     *
     * @return self
     */
    public function init(): self
    {
        return $this;
    }

    /**
     * 初始化配置
     *
     * @return void
     */
    public function initConfig(): void
    {
        $this->config = \array_merge(static::getDefaultConfig(), $this->config);
        $this->config['worker_num'] = \max(1, $this->config['worker_num'] ?? 1);
        $this->config['task_worker_num'] = $this->config['task_worker_num'] ?? 0;
    }

    /**
     * 投递task
     *
     * @param callable $callback
     * @param array $args
     * @return bool
     */
    public function task(callable $callback, array $args = []): bool
    {
        if ($this->config['task_worker_num'] <= 0) {
            return false;
        }
        return $this->taskWorker->task($callback, $args);
    }

    /**
     * @return void
     * @throws \Throwable
     */
    public function start(): void
    {
        Manager::runAll($this->objectId);
    }
    
    /**
     * @return string
     */
    public function getObjectId(): string
    {
        return $this->objectId;
    }

    /**
     * @param string $objectId
     * @return self
     */
    public function setObjectId(string $objectId): self
    {
        $this->objectId = $objectId;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return self
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * 设置配置.
     *
     * @param string|array $name
     * @param mixed $value
     * @return self
     */
    public function setConfig(string|array $name, $value = null): self
    {
        if (is_array($name)) {
            $this->config = \array_merge($this->config, $name);
        } else {
            Arr::set($this->config, $name, $value);
        }
        return $this;
    }

    /**
     * 获取配置
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getConfig(string $name, mixed $default = null): mixed
    {
        return Arr::get($this->config, $name, $default);
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
     * @return array
     */
    public static function getDefaultConfig(): array
    {
        return [
            'worker_num'      => 1,
            'task_worker_num' => 0,
        ];
    }
}