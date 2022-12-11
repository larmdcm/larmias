<?php

declare(strict_types=1);

namespace Larmias\Engine;

use Larmias\Engine\Bootstrap\WorkerStartCallback;
use Larmias\Engine\Contracts\KernelInterface;
use Larmias\Engine\Contracts\WorkerInterface;
use Psr\Container\ContainerInterface;

class Worker implements WorkerInterface
{

    /**
     * @var string
     */
    public const ON_WORKER_START = 'onWorkerStart';

    /**
     * @var array
     */
    protected array $callbacks = [];

    /**
     * @var int
     */
    protected int $workerId;

    /** @var EngineConfig */
    protected EngineConfig $engineConfig;

    /**
     * Server constructor.
     *
     * @param ContainerInterface $container
     * @param KernelInterface $kernel
     * @param WorkerConfig $workerConfig
     */
    public function __construct(protected ContainerInterface $container, protected KernelInterface $kernel, protected WorkerConfig $workerConfig)
    {
        $this->engineConfig = $this->kernel->getConfig();
        $this->initialize();
    }

    /**
     * @return void
     */
    public function initialize(): void
    {
        $this->registerEventCallback();
    }

    /**
     * @param int $workerId
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function onWorkerStart(int $workerId)
    {
        Timer::init($this->container->get($this->kernel->getDriver()->getTimerClass()));
        $this->setWorkerId($workerId);
        $this->trigger(static::ON_WORKER_START, [$this]);
    }

    /**
     *  触发回调函数
     *
     * @param string $event
     * @param array $args
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function trigger(string $event, array $args = []): void
    {
        $callable = $this->callbacks[$event] ?? null;
        if (!$callable) {
            return;
        }
        if (\is_callable($callable)) {
            $callable(...$args);
            return;
        }
        if (!\is_array($callable) || (!isset($callable[0]) && !isset($callable[1]))) {
            return;
        }
        $object = $this->container->get($callable[0]);
        \call_user_func_array([$object, $callable[1]], $args);
    }

    /**
     * @return int
     */
    public function getWorkerId(): int
    {
        return $this->workerId;
    }

    /**
     * @param int $workerId
     * @return void
     */
    public function setWorkerId(int $workerId): void
    {
        $this->workerId = $workerId;
    }

    /**
     * @return void
     */
    protected function registerEventCallback(): void
    {
        $this->callbacks = \array_merge($this->engineConfig->getCallbacks(), $this->workerConfig->getCallbacks());
        $this->callbacks[static::ON_WORKER_START] = [WorkerStartCallback::class, 'onWorkerStart'];
    }
}