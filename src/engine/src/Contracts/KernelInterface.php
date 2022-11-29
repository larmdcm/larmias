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
     * @param \Larmias\Engine\WorkerConfig $workerConfig
     * @return \Larmias\Engine\Contracts\WorkerInterface
     */
    public function addWorker(WorkerConfig $workerConfig): WorkerInterface;

    /**
     * @return void
     */
    public function run(): void;
}