<?php

declare(strict_types=1);

namespace Larmias\Framework;

use Larmias\Contracts\ApplicationInterface;
use Larmias\Di\Container;
use Larmias\Contracts\ContainerInterface;
use Larmias\Framework\Listeners\WorkerStartListener;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Larmias\Engine\Kernel;
use Larmias\Engine\EngineConfig;
use Larmias\Event\EventDispatcherFactory;
use Larmias\Event\ListenerProviderFactory;
use RuntimeException;

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
     * @var Kernel
     */
    protected Kernel $engine;

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
            ListenerProviderInterface::class => function () {
                return ListenerProviderFactory::make($this, $this->getBootListeners());
            },
            EventDispatcherInterface::class => function () {
                return EventDispatcherFactory::make($this);
            },
        ]);

        $this->engine = $this->make(Kernel::class);
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
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Throwable
     */
    public function run(): void
    {
        $configFile = $this->getConfigPath() . 'worker.php';

        if (!is_file($configFile)) {
            throw new RuntimeException(sprintf('%s The worker configuration file does not exist.', $configFile));
        }

        $this->engine->setConfig(EngineConfig::build(require $configFile));

        $this->engine->run();
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
    protected function getBootListeners(): array
    {
        return [
            WorkerStartListener::class,
        ];
    }
}