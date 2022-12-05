<?php

declare(strict_types=1);

namespace Larmias\Framework;

use Larmias\Console\Console;
use Larmias\Contracts\ApplicationInterface;
use Larmias\Contracts\ConfigInterface;
use Larmias\Contracts\ConsoleInterface;
use Larmias\Di\Container;
use Larmias\Contracts\ContainerInterface;
use Larmias\Framework\Commands\Start;
use Larmias\Framework\Listeners\WorkerStartListener;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Larmias\Event\EventDispatcherFactory;
use Larmias\Event\ListenerProviderFactory;

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
    

    /** @var bool */
    protected bool $isInitialize = false;

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
    }

    /**
     * @return void
     */
    public function initialize(): void
    {
        if ($this->isInitialize) {
            return;
        }
        $this->isInitialize = true;
    }

    /**
     * 加载配置
     *
     * @return void
     */
    public function loadConfig(): void
    {
        if (is_dir($this->configPath)) {
            foreach (glob($this->configPath . '*.*') as $filename) {
                $this->get(ConfigInterface::class)->load($filename);
            }
        }
    }


    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Throwable
     */
    public function run(): void
    {
        /** @var ConsoleInterface $console */
        $console = $this->make(ConsoleInterface::class);

        foreach ($this->getBootCommands() as $command) {
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
     * @return string[]
     */
    protected function getBootCommands(): array
    {
        $configFile = $this->getConfigPath() . 'commands.php';
        $commands = [];

        if (is_file($configFile)) {
            $commands = require $configFile;
        }

        return [
            Start::class,
            ...$commands,
        ];
    }

    /**
     * @return string[]
     */
    protected function getBootListeners(): array
    {
        return [
            WorkerStartListener::class,
        ];
    }
}