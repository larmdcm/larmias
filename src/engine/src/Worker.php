<?php

declare(strict_types=1);

namespace Larmias\Engine;

use Larmias\Engine\Bootstrap\WorkerStartCallback;
use Larmias\Engine\Contracts\KernelInterface;
use Larmias\Engine\Contracts\WorkerInterface;
use Psr\Container\ContainerInterface;
use function Larmias\Utils\data_get;

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

    /**
     * @var \Larmias\Engine\EngineConfig
     */
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
    public function start(int $workerId): void
    {
        Timer::init($this->container->get($this->kernel->getDriver()->getTimerClass()));
        $this->setWorkerId($workerId);
        $this->trigger(static::ON_WORKER_START, [$this]);
    }

    /**
     * @param string $event
     * @return bool
     */
    public function hasListen(string $event): bool
    {
        return isset($this->callbacks[$event]);
    }

    /**
     *  触发回调函数
     * @param string $event
     * @param array $args
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function trigger(string $event, array $args = []): void
    {
        if (!$this->hasListen($event)) {
            return;
        }
        $callable = $this->callbacks[$event];
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
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * @return KernelInterface
     */
    public function getKernel(): KernelInterface
    {
        return $this->kernel;
    }

    /**
     * @param string|null $name
     * @param mixed|null $default
     * @return mixed
     */
    public function getSettings(string $name = null, mixed $default = null): mixed
    {
        $config = \array_merge($this->workerConfig->getSettings(), $this->engineConfig->getSettings());
        return $name ? data_get($config, $name, $default) : $config;
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
     * @return EngineConfig
     */
    public function getEngineConfig(): EngineConfig
    {
        return $this->engineConfig;
    }

    /**
     * @return WorkerConfig
     */
    public function getWorkerConfig(): WorkerConfig
    {
        return $this->workerConfig;
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