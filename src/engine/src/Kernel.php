<?php

declare(strict_types=1);

namespace Larmias\Engine;

use Larmias\Engine\Contracts\DriverInterface;
use Larmias\Engine\Contracts\KernelInterface;
use Larmias\Engine\Contracts\WorkerInterface;
use Psr\Container\ContainerInterface;
use RuntimeException;

class Kernel implements KernelInterface
{
    /**
     * @var \Larmias\Engine\EngineConfig
     */
    protected EngineConfig $engineConfig;

    /**
     * @var \Larmias\Engine\Contracts\DriverInterface
     */
    protected DriverInterface $driver;

    /**
     * @var WorkerInterface[]
     */
    protected array $workers = [];

    /**
     * Server constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(protected ContainerInterface $container)
    {
    }

    /**
     * @param \Larmias\Engine\EngineConfig $engineConfig
     * @return self
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function setConfig(EngineConfig $engineConfig): self
    {
        $this->engineConfig = $engineConfig;
        $this->driver = $this->container->get($this->engineConfig->getDriver());
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
            $this->workers[$workerConfig->getName()] = $this->addWorker($workerConfig);
        }
        $this->driver->run($this->workers);
    }

    /**
     * @param WorkerConfig $workerConfig
     * @return \Larmias\Engine\Contracts\WorkerInterface
     */
    public function addWorker(WorkerConfig $workerConfig): WorkerInterface
    {
        $class = match ($workerConfig->getType()) {
            WorkerType::TCP_SERVER => $this->driver->getTcpServerClass(),
            WorkerType::HTTP_SERVER => $this->driver->getHttpServerClass(),
            WorkerType::WORKER_PROCESS => $this->driver->getProcessClass(),
            default => null,
        };
        if (!$class || !\class_exists($class)) {
            throw new RuntimeException('driver class not set.');
        }
        return new $class($this->container, $this, $workerConfig);
    }

    /**
     * @return EngineConfig
     */
    public function getConfig(): EngineConfig
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
}