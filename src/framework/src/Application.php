<?php

declare(strict_types=1);

namespace Larmias\Framework;

use Larmias\Console\Console;
use Larmias\Contracts\ApplicationInterface;
use Larmias\Contracts\ConsoleInterface;
use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\ServiceProviderInterface;
use Larmias\Di\Container;
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
    protected string $configPath;

    /**
     * @var string
     */
    protected string $runtimePath;

    /**
     * @var ServiceProviderInterface[]
     */
    protected array $providers = [];

    /**
     * Application constructor.
     *
     * @param string $rootPath
     * @throws \ReflectionException
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
            ConsoleInterface::class => Console::class,
            ListenerProviderInterface::class => function () {
                return ListenerProviderFactory::make($this, $this->getBootListeners());
            },
            EventDispatcherInterface::class => function () {
                return EventDispatcherFactory::make($this);
            },
        ]);

        foreach ($this->getBootProviders() as $provider) {
            $this->register($provider);
        }
    }

    /**
     * @return void
     */
    public function boot(): void
    {
        foreach ($this->providers as $provider) {
            $provider->register();
            $provider->boot();
        }
    }

    /**
     * @param ServiceProviderInterface|string $provider
     * @param bool $force
     * @return ServiceProviderInterface|null
     */
    public function register(ServiceProviderInterface|string $provider, bool $force = false): ?ServiceProviderInterface
    {
        if ($this->getServiceProvider($provider) && !$force) {
            return null;
        }
        if (\is_string($provider)) {
            $provider = new $provider($this);
        }
        $provider->initialize();
        $this->providers[\get_class($provider)] = $provider;
        return $provider;
    }

    /**
     * @param ServiceProviderInterface|string $provider
     * @return ServiceProviderInterface|null
     */
    public function getServiceProvider(ServiceProviderInterface|string $provider): ?ServiceProviderInterface
    {
        $provider = \is_object($provider) ? \get_class($provider) : $provider;
        return $this->providers[$provider] ?? null;
    }

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Throwable
     */
    public function run(): void
    {
        /** @var ConsoleInterface $console */
        $console = $this->get(ConsoleInterface::class);
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
     * @return string[]
     */
    protected function getBootProviders(): array
    {
        return [
            Providers\BootServiceProvider::class
        ];
    }

    /**
     * @return string[]
     */
    protected function getBootListeners(): array
    {
        return [
            Listeners\WorkerStartListener::class,
        ];
    }
}