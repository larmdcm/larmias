<?php

declare(strict_types=1);

namespace Larmias\Framework;

use Larmias\Contracts\ApplicationInterface;
use Larmias\Contracts\ConfigInterface;
use Larmias\Contracts\ConsoleInterface;
use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\DotEnvInterface;
use Larmias\Contracts\ServiceProviderInterface;
use Larmias\Contracts\StdoutLoggerInterface;
use Larmias\Di\Container;
use Larmias\Engine\Contracts\KernelInterface;
use Larmias\Engine\Event;
use Larmias\Engine\Kernel;
use Larmias\Engine\WorkerType;
use Larmias\Event\EventDispatcherFactory;
use Larmias\Event\ListenerProviderFactory;
use Larmias\Command\Application as ConsoleApplication;
use Larmias\Framework\Contracts\ServiceDiscoverInterface;
use Larmias\Framework\Logger\StdoutLogger;
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
    protected string $configExt = 'php';

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
     * @var array
     */
    protected array $discoverConfig = [];

    /**
     * @var ServiceDiscoverInterface
     */
    protected ServiceDiscoverInterface $serviceDiscover;

    /**
     * @var bool
     */
    protected bool $isInit = false;

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
            ApplicationInterface::class => $this,
            KernelInterface::class => Kernel::class,
            ConsoleInterface::class => ConsoleApplication::class,
            StdoutLoggerInterface::class => StdoutLogger::class,
            ServiceDiscoverInterface::class => ServiceDiscover::class,
            ListenerProviderInterface::class => function () {
                return ListenerProviderFactory::make($this, $this->loadFileConfig('listeners', false));
            },
            EventDispatcherInterface::class => function () {
                return EventDispatcherFactory::make($this);
            },
        ]);
        /** @var ServiceDiscoverInterface $serviceDiscover */
        $serviceDiscover = $this->make(ServiceDiscoverInterface::class);
        $this->serviceDiscover = $serviceDiscover;
    }

    /**
     * 初始化
     *
     * @return void
     * @throws \ReflectionException
     */
    public function initialize(): void
    {
        if ($this->isInit) {
            return;
        }
        $this->bind($this->loadFileConfig('dependencies'));
        $this->loadEnv();
        $this->loadConfig();
        \date_default_timezone_set($this->config->get('app.default_timezone', 'Asia/Shanghai'));
        $this->bind($this->config->get('dependencies', []));
        $this->boot();
        $this->isInit = true;
    }

    /**
     * 加载环境变量配置
     *
     * @return void
     * @throws \ReflectionException
     */
    protected function loadEnv(): void
    {
        $file = $this->getRootPath() . '.env';
        if (\is_file($file)) {
            /** @var DotEnvInterface $dotenv */
            $dotenv = $this->make(DotEnvInterface::class);
            $dotenv->load($file);
        }
    }

    /**
     * 加载配置
     *
     * @return void
     * @throws \ReflectionException
     */
    protected function loadConfig(): void
    {
        $configPath = $this->getConfigPath();
        if (!\is_dir($configPath)) {
            return;
        }
        /** @var ConfigInterface $config */
        $config = $this->get(ConfigInterface::class);
        $this->config = $config;
        foreach (\glob($configPath . '*' . '.' . $this->configExt) as $filename) {
            $this->config->load($filename);
        }
    }

    /**
     * @return void
     */
    protected function boot(): void
    {
        $bootProviders = \array_merge($this->loadFileConfig('providers', false), $this->config->get('providers', []));
        foreach ($bootProviders as $provider) {
            $this->register($provider);
        }

        $providers = \array_keys($this->providers);

        foreach ($providers as $provider) {
            $this->getServiceProvider($provider)->register();
        }

        foreach ($providers as $provider) {
            $this->getServiceProvider($provider)->boot();
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
        $this->serviceDiscover->discover(function () {
            $this->discoverConfig = $this->serviceDiscover->services();
            /** @var ConsoleInterface $console */
            $console = $this->get(ConsoleInterface::class);
            $commands = $this->loadFileConfig('commands');
            foreach ($commands as $command) {
                $console->addCommand($command);
            }
            $console->run();
        });
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
    public function loadFileConfig(string $name, bool $loadConfigPath = true): array
    {
        $files = [__DIR__ . '/../config/' . $name . '.php'];
        if ($loadConfigPath) {
            $files[] = $this->getConfigPath() . $name . '.' . $this->configExt;
        }
        $config = [];
        foreach ($files as $file) {
            if (!\is_file($file)) {
                continue;
            }
            $extension = \pathinfo($file, PATHINFO_EXTENSION);
            if ($extension === 'php') {
                $config = \array_merge($config, require $file);
            }
        }
        switch ($name) {
            case 'providers':
            case 'commands':
                $config = isset($this->discoverConfig[$name]) ?
                    \array_merge(\array_column($this->discoverConfig[$name], 'class'), $config) : $config;
                break;
            case 'worker':
                $processConfig = $this->discoverConfig[ServiceDiscoverInterface::SERVICE_PROCESS] ?? [];
                if (!empty($processConfig)) {
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
                break;
        }
        return $config;
    }
}