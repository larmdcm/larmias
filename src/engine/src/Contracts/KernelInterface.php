<?php

declare(strict_types=1);

namespace Larmias\Engine\Contracts;

interface KernelInterface
{
    /**
     * @return EngineConfigInterface
     */
    public function getConfig(): EngineConfigInterface;

    /**
     * @param EngineConfigInterface $engineConfig
     * @return KernelInterface
     */
    public function setConfig(EngineConfigInterface $engineConfig): KernelInterface;

    /**
     * @param WorkerConfigInterface $workerConfig
     * @return WorkerInterface|null
     */
    public function addWorker(WorkerConfigInterface $workerConfig): ?WorkerInterface;

    /**
     * @return WorkerInterface[]
     */
    public function getWorkers(): array;

    /**
     * @return DriverInterface
     */
    public function getDriver(): DriverInterface;

    /**
     * @return void
     */
    public function run(): void;

    /**
     * @param bool $force
     * @return void
     */
    public function stop(bool $force = true): void;

    /**
     * @param bool $force
     * @return void
     */
    public function restart(bool $force = true): void;

    /**
     * @param bool $force
     * @return void
     */
    public function reload(bool $force = true): void;
}