<?php

declare(strict_types=1);

namespace Larmias\Engine\Contracts;

use Larmias\Contracts\ContainerInterface;

interface WorkerInterface
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
     * @return int
     */
    public function getWorkerId(): int;

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface;

    /**
     * @return KernelInterface
     */
    public function getKernel(): KernelInterface;

    /**
     * @param string $name
     * @param mixed|null $default
     * @return mixed
     */
    public function getSettings(string $name, mixed $default = null): mixed;

    /**
     * @return EngineConfigInterface
     */
    public function getEngineConfig(): EngineConfigInterface;

    /**
     * @return WorkerConfigInterface
     */
    public function getWorkerConfig(): WorkerConfigInterface;
}