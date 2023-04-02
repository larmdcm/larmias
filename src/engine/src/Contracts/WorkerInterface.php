<?php

declare(strict_types=1);

namespace Larmias\Engine\Contracts;

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\Worker\WorkerInterface as BaseWorkerInterface;

interface WorkerInterface extends BaseWorkerInterface
{
    /**
     * @param int $workerId
     * @return void
     */
    public function start(int $workerId): void;

    /**
     * @param string $event
     * @return bool
     */
    public function hasListen(string $event): bool;

    /**
     * @param string $event
     * @param array $args
     */
    public function trigger(string $event, array $args = []): void;

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface;

    /**
     * @return KernelInterface
     */
    public function getKernel(): KernelInterface;

    /**
     * @return EngineConfigInterface
     */
    public function getEngineConfig(): EngineConfigInterface;

    /**
     * @return WorkerConfigInterface
     */
    public function getWorkerConfig(): WorkerConfigInterface;
}