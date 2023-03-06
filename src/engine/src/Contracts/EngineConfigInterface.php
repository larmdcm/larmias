<?php

declare(strict_types=1);

namespace Larmias\Engine\Contracts;

interface EngineConfigInterface
{
    /**
     * @return string
     */
    public function getDriver(): string;

    /**
     * @param string $driver
     * @return EngineConfigInterface
     */
    public function setDriver(string $driver): EngineConfigInterface;

    /**
     * @return WorkerConfigInterface[]
     */
    public function getWorkers(): array;

    /**
     * @param array $workers
     * @return EngineConfigInterface
     */
    public function setWorkers(array $workers): EngineConfigInterface;

    /**
     * @return array
     */
    public function getSettings(): array;

    /**
     * @param array $settings
     * @return EngineConfigInterface
     */
    public function setSettings(array $settings): EngineConfigInterface;

    /**
     * @return array
     */
    public function getCallbacks(): array;

    /**
     * @param array $callbacks
     * @return EngineConfigInterface
     */
    public function setCallbacks(array $callbacks): EngineConfigInterface;
}