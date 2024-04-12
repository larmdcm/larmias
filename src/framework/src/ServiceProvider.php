<?php

declare(strict_types=1);

namespace Larmias\Framework;

use Larmias\Contracts\Annotation\AnnotationInterface;
use Larmias\Contracts\ApplicationInterface;
use Larmias\Contracts\ConfigInterface;
use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\ServiceProviderInterface;
use Larmias\Contracts\ServiceDiscoverInterface;
use Larmias\Contracts\VendorPublishInterface;
use Larmias\Contracts\ViewInterface;

abstract class ServiceProvider implements ServiceProviderInterface
{
    protected ?ApplicationInterface $app = null;

    protected ?ServiceDiscoverInterface $serviceDiscover = null;

    protected ConfigInterface $config;

    /**
     * @param ContainerInterface $container
     * @throws \Throwable
     */
    public function __construct(protected ContainerInterface $container)
    {
        $this->config = $this->container->get(ConfigInterface::class);

        if (method_exists($this, 'initialize')) {
            $this->container->invoke([$this, 'initialize']);
        }

        if ($this->container->has(ApplicationInterface::class)) {
            $this->app = $this->container->get(ApplicationInterface::class);
        }

        if ($this->container->has(ServiceDiscoverInterface::class)) {
            $this->serviceDiscover = $this->container->get(ServiceDiscoverInterface::class);
        }
    }

    /**
     * @return void
     */
    public function register(): void
    {
    }

    /**
     * @return void
     */
    public function boot(): void
    {
    }

    /**
     * @param string|array $commands
     * @return void
     */
    public function commands(string|array $commands): void
    {
        $this->serviceDiscover?->commands($commands);
    }

    /**
     * @param string $process
     * @param string|null $name
     * @param int|null $num
     * @param array $options
     * @return void
     */
    public function addProcess(string $process, ?string $name = null, ?int $num = 1, array $options = []): void
    {
        $this->serviceDiscover?->addProcess($process, $name, $num, $options);
    }

    /**
     * @param string|array $listeners
     * @return void
     */
    public function listener(string|array $listeners): void
    {
        $this->serviceDiscover?->listener($listeners);
    }

    /**
     * @param string $name
     * @param array $paths
     * @return void
     * @throws \Throwable
     */
    public function publishes(string $name, array $paths): void
    {
        if (!$this->container->has(VendorPublishInterface::class)) {
            return;
        }

        /** @var VendorPublishInterface $publish */
        $publish = $this->app->getContainer()->get(VendorPublishInterface::class);
        $publish->publishes($name, $paths);
    }

    /**
     * @param string $path
     * @param string|null $namespace
     * @return void
     * @throws \Throwable
     */
    public function loadViewsFrom(string $path, ?string $namespace = null): void
    {
        if (!$this->container->has(ViewInterface::class)) {
            return;
        }

        /** @var ViewInterface $view */
        $view = $this->container->get(ViewInterface::class);
        if ($namespace) {
            $view->addNamespace($namespace, $path);
        } else {
            $view->addLocation($path);
        }
    }

    /**
     * 注册注解扫描路径
     * @param string|array $path
     * @return void
     * @throws \Throwable
     */
    public function registerAnnotationScanPath(string|array $path): void
    {
        if (!$this->container->has(AnnotationInterface::class)) {
            return;
        }
        /** @var AnnotationInterface $annotation */
        $annotation = $this->container->get(AnnotationInterface::class);
        $annotation->addIncludePath($path);
    }

    /**
     * 添加注解处理器
     * @param string|array $annotations
     * @param string $handler
     * @return void
     * @throws \Throwable
     */
    public function addAnnotationHandler(string|array $annotations, string $handler): void
    {
        if (!$this->container->has(AnnotationInterface::class)) {
            return;
        }
        /** @var AnnotationInterface $annotation */
        $annotation = $this->container->get(AnnotationInterface::class);
        $annotation->addHandler($annotations, $handler);
    }
}