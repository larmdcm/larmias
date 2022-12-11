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
     * @return EngineConfig
     */
    public function getConfig(): EngineConfig;

    /**
     * @param \Larmias\Engine\WorkerConfig $workerConfig
     * @return \Larmias\Engine\Contracts\WorkerInterface
     */
    public function addWorker(WorkerConfig $workerConfig): WorkerInterface;

    /**
     * @return DriverInterface
     */
    public function getDriver(): DriverInterface;

    /**
     * @return void
     */
    public function run(): void;
}