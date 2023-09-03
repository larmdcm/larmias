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
use Larmias\Engine\Contracts\KernelInterface;
use Larmias\Engine\Kernel;
use Larmias\Env\DotEnv;
use Larmias\Event\EventDispatcherFactory;
use Larmias\Event\ListenerProviderFactory;
use Larmias\Command\Application as ConsoleApplication;
use Larmias\Contracts\ServiceDiscoverInterface;
use Larmias\Framework\Listeners\WorkerStartListener;
use Larmias\Framework\Logger\StdoutLogger;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Throwable;
use function rtrim;
use function dirname;
use function date_default_timezone_set;
use function is_file;
use function is_dir;
use function glob;
use function array_keys;
use function array_merge;
use function array_column;
use function is_object;

class Application implements ApplicationInterface
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
     * @var ServiceProviderInterface[]|string[]
     */
    protected array $providers = [];

    /**
     * @var ConfigInterface
     */
    protected ConfigInterface $config;

    /**
     * @var ServiceDiscoverInterface
     */
    protected ServiceDiscoverInterface $serviceDiscover;

    /**
     * @var array
     */
    protected array $discoverConfig = [];

    /**
     * @var bool
     */
    protected bool $isInit = false;

    /**
     * @param ContainerInterface $container
     * @param string $rootPath
     */
    public function __construct(protected ContainerInterface $container, string $rootPath = '')
    {
        $this->rootPath = rtrim($rootPath ?: dirname(realpath($rootPath))) . DIRECTORY_SEPARATOR;
        $this->configPath = $this->rootPath . 'config' . DIRECTORY_SEPARATOR;
        $this->runtimePath = $this->rootPath . 'runtime' . DIRECTORY_SEPARATOR;
        $this->container->bindIf([
            KernelInterface::class => Kernel::class,
            ConsoleInterface::class => ConsoleApplication::class,
            StdoutLoggerInterface::class => StdoutLogger::class,
            ServiceDiscoverInterface::class => ServiceDiscover::class,
            DotEnvInterface::class => DotEnv::class,
            ListenerProviderInterface::class => function () {
                return ListenerProviderFactory::make($this->container, [
                    WorkerStartListener::class
                ]);
            },
            EventDispatcherInterface::class => function () {
                return EventDispatcherFactory::make($this->container);
            },
        ]);
    }

    /**
     * 初始化
     * @return void
     * @throws Throwable
     */
    public function initialize(): void
    {
        if ($this->isInit) {
            return;
        }
        $this->container->bindIf($this->getServiceConfig('dependencies'));
        $this->loadConfig();
        date_default_timezone_set($this->config->get('app.default_timezone', 'Asia/Shanghai'));
        $this->container->bindIf($this->config->get('dependencies', []));
        $this->boot();
        $this->isInit = true;
    }

    /**
     * 加载环境变量配置
     * @return void
     */
    protected function loadEnv(): void
    {
        $file = $this->getRootPath() . '.env';
        if (is_file($file)) {
            /** @var DotEnvInterface $dotenv */
            $dotenv = $this->container->make(DotEnvInterface::class);
            $dotenv->load($file);
        }
    }

    /**
     * 加载配置
     * @return void
     * @throws Throwable
     */
    protected function loadConfig(): void
    {
        $configPath = $this->getConfigPath();
        if (!is_dir($configPath)) {
            return;
        }
        /** @var ConfigInterface $config */
        $config = $this->container->get(ConfigInterface::class);
        $this->config = $config;
        foreach (glob($configPath . '*' . '.' . $this->configExt) as $filename) {
            $this->config->load($filename);
        }
    }

    /**
     * 注册启动服务提供者
     * @return void
     * @throws Throwable
     */
    protected function boot(): void
    {
        $bootProviders = array_merge($this->config->get('app.providers', []), $this->getServiceConfig(ServiceDiscoverInterface::SERVICE_PROVIDER));

        foreach ($bootProviders as $provider) {
            $this->register($provider);
        }

        $providers = array_keys($this->providers);

        foreach ($providers as $provider) {
            $this->getServiceProvider($provider)->register();
        }

        foreach ($providers as $provider) {
            $this->getServiceProvider($provider)->boot();
        }
    }

    /**
     * 注册服务提供者
     * @param string $provider
     * @param bool $force
     * @return ApplicationInterface
     */
    public function register(string $provider, bool $force = false): ApplicationInterface
    {
        if (isset($this->providers[$provider]) && !$force) {
            return $this;
        }

        $this->providers[$provider] = $provider;

        return $this;
    }

    /**
     * 获取服务提供者
     * @param string $provider
     * @return ServiceProviderInterface|null
     * @throws Throwable
     */
    public function getServiceProvider(string $provider): ?ServiceProviderInterface
    {
        if (!isset($this->providers[$provider])) {
            return null;
        }

        if (!is_object($this->providers[$provider])) {
            $this->providers[$provider] = $this->container->get($this->providers[$provider]);
        }

        return $this->providers[$provider];
    }

    /**
     * 运行
     * @throws Throwable
     */
    public function run(): void
    {
        $this->serviceDiscover = $this->container->get(ServiceDiscoverInterface::class);
        $this->loadEnv();
        $this->serviceDiscover->discover(function () {
            $this->discoverConfig = $this->serviceDiscover->services();
            /** @var ConsoleInterface $console */
            $console = $this->container->get(ConsoleInterface::class);
            $commands = $this->getServiceConfig(ServiceDiscoverInterface::SERVICE_COMMAND, true);
            foreach ($commands as $command) {
                $console->addCommand($command);
            }
            $console->run();
        });
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
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
        $this->rootPath = rtrim($rootPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
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
        $this->configPath = rtrim($configPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
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
        $this->runtimePath = rtrim($runtimePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
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
     * 获取服务配置
     * @param string $name
     * @param bool $loadConfigPath
     * @return array
     */
    public function getServiceConfig(string $name, bool $loadConfigPath = false): array
    {
        $getConfig = function () use ($name, $loadConfigPath) {
            $files = [__DIR__ . '/../config/' . $name . '.php'];
            if ($loadConfigPath && $this->configExt === 'php') {
                $files[] = $this->getConfigPath() . $name . '.' . $this->configExt;
            }
            $config = [];
            foreach ($files as $file) {
                if (!is_file($file)) {
                    continue;
                }
                $config = array_merge($config, require $file);
            }
            return $config;
        };

        $config = $getConfig();

        return match ($name) {
            'providers', 'commands', 'listeners' => array_merge($config, isset($this->discoverConfig[$name]) ? array_column($this->discoverConfig[$name], 'class') : []),
        };
    }


    /**
     * @param string|null $name
     * @param mixed|null $default
     * @return array
     */
    public function getDiscoverConfig(?string $name = null, mixed $default = null): array
    {
        return $name ? $this->discoverConfig[$name] ?? $default : $this->discoverConfig;
    }
}