<?php

declare(strict_types=1);

namespace Larmias\Framework;

use Larmias\Contracts\ApplicationInterface;
use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\ServiceProviderInterface;
use Larmias\Contracts\ServiceDiscoverInterface;
use Larmias\Contracts\VendorPublishInterface;
use Larmias\Contracts\ViewInterface;

abstract class ServiceProvider implements ServiceProviderInterface
{
    protected ContainerInterface $container;

    /**
     * @param ApplicationInterface $app
     * @param ServiceDiscoverInterface $serviceDiscover
     */
    public function __construct(protected ApplicationInterface $app, protected ServiceDiscoverInterface $serviceDiscover)
    {
        $this->container = $this->app->getContainer();
        if (method_exists($this, 'initialize')) {
            $this->app->getContainer()->invoke([$this, 'initialize']);
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
        $this->serviceDiscover->commands($commands);
    }

    /**
     * @param string $process
     * @param string $name
     * @param int $count
     * @return void
     */
    public function addProcess(string $process, string $name, int $count = 1): void
    {
        $this->serviceDiscover->addProcess($process, $name, $count);
    }

    /**
     * @param string|array $listeners
     * @return void
     */
    public function listener(string|array $listeners): void
    {
        $this->serviceDiscover->listener($listeners);
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
}