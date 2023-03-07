<?php

declare(strict_types=1);

namespace Larmias\Engine\Contracts;

use Larmias\Engine\EngineConfig;
use Larmias\Engine\WorkerConfig;

interface KernelInterface
{
    /**
     * @param EngineConfig $engineConfig
     * @return KernelInterface
     */
    public function setConfig(EngineConfig $engineConfig): KernelInterface;

    /**
     * @return EngineConfigInterface
     */
    public function getConfig(): EngineConfigInterface;

    /**
     * @param WorkerConfig $workerConfig
     * @return WorkerInterface
     */
    public function addWorker(WorkerConfig $workerConfig): WorkerInterface;

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