<?php

declare(strict_types=1);

namespace Larmias\Framework\Commands\Concerns;

use RuntimeException;
use function method_exists;
use function is_file;

trait WorkerConfig
{
    /**
     * @return array
     */
    protected function getWorkerConfig(): array
    {
        if (method_exists($this->app, 'getServiceConfig')) {
            $config = $this->app->getServiceConfig('worker', true);
        } else {
            $configFile = $this->app->getConfigPath() . 'worker.php';
            if (!is_file($configFile)) {
                throw new RuntimeException(sprintf('%s The worker configuration file does not exist.', $configFile));
            }
            $config = require $configFile;
        }

        return $config;
    }
}