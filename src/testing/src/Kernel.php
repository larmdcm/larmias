<?php

declare(strict_types=1);

namespace Larmias\Testing;

use Larmias\Engine\Contracts\EngineConfigInterface;
use Larmias\Engine\Contracts\WorkerInterface;
use Larmias\Engine\Kernel as BaseKernel;
use Larmias\Engine\WorkerConfig;

class Kernel extends BaseKernel
{
    /**
     * @return void
     */
    protected function initWorkers(): void
    {
        $this->workers['main'] = $this->getMainWorker();
    }

    public function setConfig(EngineConfigInterface $engineConfig): BaseKernel
    {
        $settings = $engineConfig->getSettings();
        $settings['testing'] = true;
        $engineConfig->setSettings($settings);
        return BaseKernel::setConfig($engineConfig);
    }

    /**
     * @return WorkerInterface
     */
    protected function getMainWorker(): WorkerInterface
    {
        return new Worker($this->container, $this, WorkerConfig::build([]));
    }
}