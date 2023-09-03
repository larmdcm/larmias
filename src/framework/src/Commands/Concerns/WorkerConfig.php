<?php

declare(strict_types=1);

namespace Larmias\Framework\Commands\Concerns;

use Larmias\Contracts\ServiceDiscoverInterface;
use Larmias\Engine\Event;
use Larmias\Engine\WorkerType;
use RuntimeException;
use function method_exists;
use function is_file;

trait WorkerConfig
{
    /**
     * 获取引擎配置
     * @param string $name
     * @return array
     */
    protected function getEngineConfig(string $name = 'engine'): array
    {
        if (method_exists($this->app, 'getServiceConfig')) {
            $config = $this->app->getServiceConfig($name, true);
        } else {
            $configFile = $this->app->getConfigPath() . $name . '.php';
            if (!is_file($configFile)) {
                throw new RuntimeException(sprintf('%s The engine configuration file does not exist.', $configFile));
            }
            $config = require $configFile;
        }

        if (method_exists($this->app, 'getDiscoverConfig')) {
            $processConfig = $this->app->getDiscoverConfig(ServiceDiscoverInterface::SERVICE_PROCESS, []);
            foreach ($processConfig as $item) {
                $config['workers'][] = [
                    'name' => $item['args']['name'],
                    'type' => WorkerType::WORKER_PROCESS,
                    'settings' => ['worker_num' => $item['args']['count']],
                    'callbacks' => [
                        Event::ON_WORKER_START => [$item['class'], 'onStart'],
                        Event::ON_WORKER => [$item['class'], 'handle'],
                    ],
                ];
            }
        }

        return $config;
    }
}