<?php

declare(strict_types=1);

namespace Larmias\Engine;

use Larmias\Engine\Contracts\DriverInterface;
use Larmias\Engine\Contracts\KernelInterface;
use Psr\Container\ContainerInterface;
use RuntimeException;

class Kernel implements KernelInterface
{
    /** @var EngineConfig */
    protected EngineConfig $engineConfig;

    /** @var DriverConfig */
    protected DriverConfig $driverConfig;

    /**
     * Server constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(protected ContainerInterface $container)
    {
        DriverConfigManager::init();
    }

    /**
     * @param EngineConfig $engineConfig
     * @return self
     */
    public function setConfig(EngineConfig $engineConfig): self
    {
        $this->engineConfig = $engineConfig;
        $this->driverConfig = DriverConfigManager::get($this->engineConfig->getDriver());
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
            $this->makeWorker($workerConfig);
        }
         $this->makeDriver()->run();
    }

    /**
     * @return DriverInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    protected function makeDriver(): DriverInterface
    {
        $driver = $this->driverConfig->getDriver();
        return $this->container->get($driver);
    }

    /**
     * @param WorkerConfig $workerConfig
     * @return object
     */
    protected function makeWorker(WorkerConfig $workerConfig): object
    {
        $class = match ($workerConfig->getType()) {
            WorkerType::HTTP_SERVER => $this->driverConfig->getHttpServer(),
            default => null,
        };
        if (!$class || !class_exists($class)) {
            throw new RuntimeException('driver class not set.');
        }
        return new $class($this->container,$this->engineConfig,$workerConfig);
    }
}