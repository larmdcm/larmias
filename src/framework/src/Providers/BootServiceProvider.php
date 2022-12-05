<?php

declare(strict_types=1);

namespace Larmias\Framework\Providers;

use Larmias\Config\Config;
use Larmias\Contracts\ConfigInterface;
use Larmias\Framework\ServiceProvider;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class BootServiceProvider extends ServiceProvider
{
    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function initialize(): void
    {
        $this->commands($this->getBootCommands());
    }

    /**
     * @return void
     */
    public function register(): void
    {
        $this->app->bind([
            ConfigInterface::class => Config::class,
        ]);
    }

    /**
     * @return void
     */
    public function boot(): void
    {
        $this->loadConfig();
    }

    /**
     * 加载配置
     *
     * @return void
     */
    protected function loadConfig(): void
    {
        $configPath = $this->app->getConfigPath();
        if (\is_dir($configPath)) {
            /** @var ConfigInterface $config */
            $config = config();
            foreach (\glob($configPath . '*.*') as $filename) {
                $config->load($filename);
            }
        }
    }

    /**
     * @return string[]
     */
    protected function getBootCommands(): array
    {
        $configFile = $this->app->getConfigPath() . 'commands.php';
        $commands = [];

        if (is_file($configFile)) {
            $commands = require $configFile;
        }

        return [
            \Larmias\Framework\Commands\Start::class,
            ...$commands,
        ];
    }
}