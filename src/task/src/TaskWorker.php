<?php

declare(strict_types=1);

namespace Larmias\Task;

use Larmias\Contracts\ContainerInterface;
use Larmias\Task\Enum\WorkerStatus;
use Larmias\Task\Client\Client;
use Throwable;
use function Larmias\Utils\format_exception;
use function Larmias\Utils\println;
use function array_merge;
use function is_callable;
use function is_string;
use function explode;
use function method_exists;

class TaskWorker
{
    /**
     * @var array
     */
    protected array $config = [
        'host' => '127.0.0.1',
        'port' => 2000,
        'password' => '',
    ];

    /**
     * @var Client
     */
    protected Client $client;

    /**
     * @var int
     */
    protected int $workerId;

    /**
     * @var string
     */
    protected string $name;

    /**
     * @var int
     */
    protected int $status = WorkerStatus::IDLE;

    /**
     * TaskWorker constructor.
     * @param ContainerInterface $container
     * @param array $config
     */
    public function __construct(protected ContainerInterface $container, array $config = [])
    {
        $this->config = array_merge($this->config, $config);
        $this->client = new Client($this->config);
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
     * @return int
     */
    public function status(): int
    {
        return $this->status;
    }

    /**
     * @param int $status
     * @return self
     */
    public function setStatus(int $status): self
    {
        $this->status = $status;
        $this->client->setInfo('status', $this->status);
        return $this;
    }

    /**
     * @return void
     */
    public function run(): void
    {
        $this->client->subscribe($this->getName(), [
            function () {
                $this->setStatus(WorkerStatus::IDLE);
            },
            function (array $data) {
                try {
                    $this->status = WorkerStatus::RUNNING;
                    $this->runTask(Task::parse($data['task']));
                } catch (Throwable $e) {
                    println(format_exception($e));
                } finally {
                    $this->setStatus(WorkerStatus::IDLE);
                }
            }
        ]);
    }

    /**
     * @param Task $task
     * @return void
     */
    protected function runTask(Task $task): void
    {
        $handler = $task->getHandler();
        if (!is_callable($handler)) {
            if (is_string($handler)) {
                $handler = explode('@', $handler);
            }
            $instance = $this->container->make($handler[0], [], true);
            $handler = [$instance, $handler[1]];
        }
        try {
            $result = $this->container->invoke($handler, $task->getArgs());
            if (isset($instance) && method_exists($instance, 'onFinish')) {
                $instance->onFinish($task, $result);
            }
        } catch (Throwable $e) {
            if (isset($instance) && method_exists($instance, 'onException')) {
                $instance->onException($task, $e);
            }
        }
    }

    public function __destruct()
    {
        $this->client->close();
    }
}