<?php

declare(strict_types=1);

namespace Larmias\Engine;

use Larmias\Engine\Bootstrap\BeforeStartCallback;
use Larmias\Engine\Contracts\DriverInterface;
use Larmias\Engine\Contracts\EngineConfigInterface;
use Larmias\Engine\Contracts\WorkerConfigInterface;
use Larmias\Engine\Contracts\KernelInterface;
use Larmias\Engine\Contracts\WorkerInterface;
use Larmias\Contracts\ContainerInterface;
use RuntimeException;
use function class_exists;
use function count;

class Kernel implements KernelInterface
{
    /**
     * @var EngineConfigInterface
     */
    protected EngineConfigInterface $engineConfig;

    /**
     * @var DriverInterface
     */
    protected DriverInterface $driver;

    /**
     * @var WorkerInterface[]
     */
    protected array $workers = [];

    /**
     * Kernel constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(protected ContainerInterface $container)
    {
    }

    /**
     * @param EngineConfigInterface $engineConfig
     * @return self
     * @throws \Throwable
     */
    public function setConfig(EngineConfigInterface $engineConfig): self
    {
        $this->engineConfig = $engineConfig;
        $this->driver = $this->container->get($this->engineConfig->getDriver());
        $this->driver->setConfig($this->engineConfig->getSettings());
        return $this;
    }

    /**
     * @return void
     * @throws \Throwable
     */
    public function run(): void
    {
        $workers = $this->engineConfig->getWorkers();
        foreach ($workers as $workerConfig) {
            if (!$this->addWorker($workerConfig)) {
                break;
            }
        }
        $this->container->invoke([BeforeStartCallback::class, 'onBeforeStart'], [$this]);
        $this->driver->run($this);
    }

    /**
     * @param WorkerConfigInterface $workerConfig
     * @return WorkerInterface|null
     */
    public function addWorker(WorkerConfigInterface $workerConfig): ?WorkerInterface
    {
        if (!is_unix() && count($this->workers) > 0) {
            return null;
        }

        $class = match ($workerConfig->getType()) {
            WorkerType::TCP_SERVER => $this->driver->getTcpServerClass(),
            WorkerType::UPD_SERVER => $this->driver->getUdpServerClass(),
            WorkerType::HTTP_SERVER => $this->driver->getHttpServerClass(),
            WorkerType::WEBSOCKET_SERVER => $this->driver->getWebSocketServerClass(),
            WorkerType::WORKER_PROCESS => $this->driver->getProcessClass(),
            default => null,
        };

        if (!$class || !class_exists($class)) {
            throw new RuntimeException('not support: ' . WorkerType::getName($workerConfig->getType()));
        }

        return $this->workers[$workerConfig->getName()] = new $class($this->container, $this, $workerConfig);
    }

    /**
     * @return WorkerInterface[]
     */
    public function getWorkers(): array
    {
        return $this->workers;
    }

    /**
     * @return EngineConfigInterface
     */
    public function getConfig(): EngineConfigInterface
    {
        return $this->engineConfig;
    }

    /**
     * @return DriverInterface
     */
    public function getDriver(): DriverInterface
    {
        return $this->driver;
    }

    /**
     * @param bool $force
     * @return void
     */
    public function stop(bool $force = true): void
    {
        $this->driver->stop($force);
    }

    /**
     * @param bool $force
     * @return void
     * @throws \Throwable
     */
    public function restart(bool $force = true): void
    {
        $this->driver->restart($force);
        $this->run();
    }

    /**
     * @param bool $force
     * @return void
     */
    public function reload(bool $force = true): void
    {
        $this->driver->reload($force);
    }
}