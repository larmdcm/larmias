<?php

declare(strict_types=1);

namespace Larmias\Engine;

use Larmias\Engine\Bootstrap\BeforeStartCallback;
use Larmias\Engine\Bootstrap\WorkerStartCallback;
use Larmias\Engine\Contracts\KernelInterface;
use Larmias\Engine\Contracts\WorkerInterface;
use Larmias\Contracts\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
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
     * @var EngineConfig
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
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function start(int $workerId): void
    {
        EventLoop::init($this->container->get($this->kernel->getDriver()->getEventLoopClass()));
        Timer::init($this->container->get($this->kernel->getDriver()->getTimerClass()));
        $this->setWorkerId($workerId);
        $this->container->bind(WorkerInterface::class, $this);
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
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function trigger(string $event, array $args = []): void
    {
        if (!$this->hasListen($event)) {
            return;
        }
        $items = $this->callbacks[$event];

        foreach ($items as $item) {
            if (\is_callable($item)) {
                $this->container->invoke($item, $args);
            } else {
                $object = $this->container->get($item[0]);
                \call_user_func_array([$object, $item[1]], $args);
            }
        }
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
        $this->callbacks = $this->mergeCallbacks($this->engineConfig->getCallbacks(), $this->workerConfig->getCallbacks(), [
            static::ON_WORKER_START => [WorkerStartCallback::class, 'onWorkerStart']
        ]);
        if (!$this->hasListen(Event::ON_BEFORE_START)) {
            $this->callbacks[Event::ON_BEFORE_START] = [[BeforeStartCallback::class, 'onBeforeStart']];
        }
    }

    /**
     * @param ...$args
     * @return array
     */
    protected function mergeCallbacks(...$args): array
    {
        $callbacks = [];
        foreach ($args as $argItem) {
            foreach ($argItem as $name => $callable) {
                if (!isset($callbacks[$name])) {
                    $callbacks[$name] = [];
                }
                $items = \is_array($callable) && \is_array($callable[0]) ? $callable : [$callable];
                $callbacks[$name] = \array_merge($callbacks[$name], $items);
            }
        }

        return $callbacks;
    }
}