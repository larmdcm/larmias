<?php

declare(strict_types=1);

namespace Larmias\Framework;

use Larmias\Console\Console;
use Larmias\Contracts\ApplicationInterface;
use Larmias\Contracts\ConfigInterface;
use Larmias\Contracts\ConsoleInterface;
use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\ServiceProviderInterface;
use Larmias\Di\Container;
use Larmias\Engine\Contracts\KernelInterface;
use Larmias\Engine\Kernel;
use Larmias\Event\EventDispatcherFactory;
use Larmias\Event\ListenerProviderFactory;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;

class Application extends Container implements ApplicationInterface
{
    /**
     * @var string
     */
    protected string $rootPath;

    /**
     * @var string
     */
    protected string $configExt = '.php';

    /**
     * @var string
     */
    protected string $configPath;

    /**
     * @var string
     */
    protected string $runtimePath;

    /**
     * @var ServiceProviderInterface|string[]
     */
    protected array $providers = [];

    /**
     * @var ConfigInterface
     */
    protected ConfigInterface $config;

    /**
     * Application constructor.
     *
     * @param string $rootPath
     * @throws \Throwable
     */
    public function __construct(string $rootPath = '')
    {
        parent::__construct();
        $this->rootPath = \rtrim($rootPath ?: dirname(realpath($rootPath))) . DIRECTORY_SEPARATOR;
        $this->configPath = $this->rootPath . 'config' . DIRECTORY_SEPARATOR;
        $this->runtimePath = $this->rootPath . 'runtime' . DIRECTORY_SEPARATOR;

        static::setInstance($this);
        $this->bind([
            self::class => $this,
            ContainerInterface::class => $this,
            PsrContainerInterface::class => $this,
            KernelInterface::class => Kernel::class,
            ApplicationInterface::class => $this,
            ConsoleInterface::class => Console::class,
            ListenerProviderInterface::class => function () {
                return ListenerProviderFactory::make($this, $this->loadFileConfig('listeners',false));
            },
            EventDispatcherInterface::class => function () {
                return EventDispatcherFactory::make($this);
            },
        ]);
    }

    /**
     * 初始化
     *
     * @return void
     */
    public function initialize(): void
    {
        $this->bind($this->loadFileConfig('dependencies'));
        $this->loadConfig();
        \date_default_timezone_set($this->config->get('app.default_timezone','Asia/Shanghai'));
        $this->bind($this->config->get('dependencies',[]));
        $this->boot();
    }

    /**
     * 加载配置
     *
     * @return void
     */
    protected function loadConfig(): void
    {
        $configPath = $this->getConfigPath();
        if (!\is_dir($configPath)) {
            return;
        }
        $this->config = $this->get(ConfigInterface::class);
        foreach (\glob($configPath . '*' . $this->configExt) as $filename) {
            $this->config->load($filename);
        }
    }

    /**
     * @return void
     */
    protected function boot(): void
    {
        $bootProviders = \array_merge($this->loadFileConfig('providers',false),$this->config->get('providers',[]));
        foreach ($bootProviders as $provider) {
            $this->register($provider);
        }

        foreach ($this->providers as $provider) {
            $service = $this->getServiceProvider($provider);
            $service->register();
            $service->boot();
        }
    }

    /**
     * @param string $provider
     * @param bool $force
     * @return ApplicationInterface
     */
    protected function register(string $provider, bool $force = false): ApplicationInterface
    {
        if (isset($this->providers[$provider]) && !$force) {
            return $this;
        }
        $this->providers[$provider] = $provider;
        return $this;
    }

    /**
     * @param string $provider
     * @return ServiceProviderInterface|null
     */
    public function getServiceProvider(string $provider): ?ServiceProviderInterface
    {
        if (!isset($this->providers[$provider])) {
            return null;
        }
        if (!\is_object($this->providers[$provider])) {
            $this->providers[$provider] = $this->get($this->providers[$provider]);
        }
        return $this->providers[$provider];
    }

    /**
     * @throws \Throwable
     */
    public function run(): void
    {
        /** @var ConsoleInterface $console */
        $console = $this->get(ConsoleInterface::class);
        $commands = $this->loadFileConfig('commands');
        foreach ($commands as $command) {
            $console->addCommand($command);
        }
        $console->run();
    }

    /**
     * Get the value of rootPath
     *
     * @return string
     */
    public function getRootPath(): string
    {
        return $this->rootPath;
    }

    /**
     * Set the value of rootPath
     *
     * @param string $rootPath
     * @return self
     */
    public function setRootPath(string $rootPath): self
    {
        $this->rootPath = $rootPath;
        return $this;
    }

    /**
     * Get the value of configPath
     *
     * @return  string
     */
    public function getConfigPath(): string
    {
        return $this->configPath;
    }

    /**
     * Set the value of configPath
     *
     * @param string $configPath
     * @return self
     */
    public function setConfigPath(string $configPath): self
    {
        $this->configPath = $configPath;
        return $this;
    }

    /**
     * Get the value of runtimePath
     *
     * @return string
     */
    public function getRuntimePath(): string
    {
        return $this->runtimePath;
    }

    /**
     * Set the value of runtimePath
     *
     * @param string $runtimePath
     * @return self
     */
    public function setRuntimePath(string $runtimePath): self
    {
        $this->runtimePath = $runtimePath;
        return $this;
    }

    /**
     * @return string
     */
    public function getConfigExt(): string
    {
        return $this->configExt;
    }

    /**
     * @param string $configExt
     * @return self
     */
    public function setConfigExt(string $configExt): self
    {
        $this->configExt = $configExt;
        return $this;
    }

    /**
     * @param string $name
     * @param bool $loadConfigPath
     * @return array
     */
    public function loadFileConfig(string $name,bool $loadConfigPath = true): array
    {
        $files = [__DIR__ . '/../config/' . $name . '.php'];
        if ($loadConfigPath) {
            $files[] = $this->getConfigPath() . $name . $this->configExt;
        }
        $config = [];
        foreach ($files as $file) {
            if (!\is_file($file)) {
                continue;
            }
            $extension = pathinfo($file,PATHINFO_EXTENSION);
            if ($extension === 'php') {
                $config = \array_merge($config,require $file);
            }
        }
        return $config;
    }
}