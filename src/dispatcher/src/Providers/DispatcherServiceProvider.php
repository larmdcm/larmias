<?php

declare(strict_types=1);

namespace Larmias\Dispatcher\Providers;

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\Dispatcher\DispatcherFactoryInterface;
use Larmias\Contracts\ServiceProviderInterface;
use Larmias\Dispatcher\DispatcherFactory;

class DispatcherServiceProvider implements ServiceProviderInterface
{
    public function __construct(protected ContainerInterface $container)
    {

    }

    public function register(): void
    {
        $this->container->bindIf([
            DispatcherFactoryInterface::class => DispatcherFactory::class,
        ]);
    }

    public function boot(): void
    {
    }
}