<?php

declare(strict_types=1);

namespace Larmias\Engine\Contracts;

use Larmias\Engine\EngineConfig;

interface KernelInterface
{
    /**
     * @param EngineConfig $engineConfig
     * @return KernelInterface
     */
    public function setConfig(EngineConfig $engineConfig): KernelInterface;

    /**
     * @return void
     */
    public function run(): void;
}