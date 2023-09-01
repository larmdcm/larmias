<?php

declare(strict_types=1);

namespace Larmias\Engine;

use Larmias\Contracts\ContextInterface;
use Larmias\Contracts\Coroutine\ChannelFactoryInterface;
use Larmias\Contracts\Coroutine\CoroutineFactoryInterface;
use Larmias\Contracts\EventLoopInterface;
use Larmias\Contracts\SignalInterface;
use Larmias\Contracts\TimerInterface;
use Larmias\Engine\Bootstrap\WorkerStartCallback;
use Larmias\Engine\Contracts\EngineConfigInterface;
use Larmias\Engine\Contracts\KernelInterface;
use Larmias\Engine\Contracts\WorkerConfigInterface;
use Larmias\Engine\Contracts\WorkerInterface;
use Larmias\Contracts\Worker\WorkerInterface as BaseWorkerInterface;
use Larmias\Contracts\ContainerInterface;
use Larmias\Engine\Coroutine\Channel;
use Larmias\Engine\Factory\ChannelFactory;
use Larmias\Engine\Factory\CoroutineFactory;
use function Larmias\Utils\data_get;
use function extension_loaded;
use function is_array;
use function mt_srand;
use function is_callable;
use function array_merge;
use function call_user_func;

abstract class Worker implements WorkerInterface
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
     * @var EngineConfigInterface
     */
    protected EngineConfigInterface $engineConfig;

    /**
     * Server constructor.
     *
     * @param ContainerInterface $container
     * @param KernelInterface $kernel
     * @param WorkerConfigInterface $workerConfig
     */
    public function __construct(protected ContainerInterface $container, protected KernelInterface $kernel, protected WorkerConfigInterface $workerConfig)
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
     * @throws \Throwable
     */
    public function start(int $workerId): void
    {
        $this->setWorkerId($workerId);
        $this->reset();
        $this->bind();
        $this->trigger(static::ON_WORKER_START, [$this]);
    }

    /**
     * @return void
     * @throws \Throwable
     */
    protected function bind(): void
    {
        $bind = [
            EventLoopInterface::class => $this->kernel->getDriver()->getEventLoopClass(),
            ContextInterface::class => $this->kernel->getDriver()->getContextClass(),
            SignalInterface::class => $this->kernel->getDriver()->getSignalClass(),
            TimerInterface::class => $this->kernel->getDriver()->getTimerClass(),
            CoroutineFactoryInterface::class => CoroutineFactory::class,
            ChannelFactoryInterface::class => ChannelFactory::class,
            BaseWorkerInterface::class => $this,
            WorkerInterface::class => $this,
        ];

        $init = [
            EventLoop::class => EventLoopInterface::class,
            Context::class => ContextInterface::class,
            Signal::class => SignalInterface::class,
            Timer::class => TimerInterface::class,
        ];

        $this->container->bind($bind);

        Coroutine::init($this->kernel->getDriver()->getCoroutineClass());
        Channel::init($this->kernel->getDriver()->getChannelClass());

        foreach ($init as $name => $value) {
            if ($this->container->has($value)) {
                call_user_func([$name, 'init'], $this->container->get($value));
            }
        }
    }

    /**
     * @return void
     */
    protected function reset(): void
    {
        mt_srand();
        if (extension_loaded('apc') && function_exists('apc_clear_cache')) {
            apc_clear_cache();
        }
        if (extension_loaded('Zend OPcache') && function_exists('opcache_reset')) {
            opcache_reset();
        }
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
     * 触发回调函数
     * @param string $event
     * @param array $args
     * @throws \Throwable
     */
    public function trigger(string $event, array $args = []): void
    {
        if (!$this->hasListen($event)) {
            return;
        }

        $callbacks = $this->callbacks[$event];

        foreach ($callbacks as $callback) {
            if (is_callable($callback)) {
                $this->container->invoke($callback, $args);
            } else {
                $object = $this->container->get($callback[0]);
                $this->container->invoke([$object, $callback[1]], $args);
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
        $config = array_merge($this->workerConfig->getSettings(), $this->engineConfig->getSettings());
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
     * @return EngineConfigInterface
     */
    public function getEngineConfig(): EngineConfigInterface
    {
        return $this->engineConfig;
    }

    /**
     * @return WorkerConfigInterface
     */
    public function getWorkerConfig(): WorkerConfigInterface
    {
        return $this->workerConfig;
    }

    /**
     * @return void
     */
    protected function registerEventCallback(): void
    {
        $defaultCallbacks[static::ON_WORKER_START] = [WorkerStartCallback::class, 'onWorkerStart'];
        $this->callbacks = $this->mergeCallbacks($this->engineConfig->getCallbacks(), $this->workerConfig->getCallbacks(), $defaultCallbacks);
    }

    /**
     * @param ...$args
     * @return array
     */
    protected function mergeCallbacks(...$args): array
    {
        $callbacks = [];
        $queueMap = [];
        foreach ($args as $argItem) {
            foreach ($argItem as $name => $callable) {
                $isQueue = is_array($callable) && (is_array($callable[0]) || is_callable($callable[0]));
                $existsQueue = isset($queueMap[$name]);
                if (empty($callbacks[$name])) {
                    $callbacks[$name] = $isQueue ? $callable : [$callable];
                    if ($isQueue) {
                        $queueMap[$name] = true;
                    }
                } else {
                    if ($isQueue) {
                        $callbacks[$name] = [...$callbacks[$name], ...$callable];
                        $queueMap[$name] = true;
                    } else {
                        if ($existsQueue) {
                            $callbacks[$name] = [...$callbacks[$name], ...[$callable]];
                        } else {
                            $callbacks[$name] = [$callable];
                        }
                    }
                }
            }
        }
        return $callbacks;
    }
}