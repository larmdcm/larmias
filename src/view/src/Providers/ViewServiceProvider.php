<?php

declare(strict_types=1);

namespace Larmias\View\Providers;

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\ServiceProviderInterface;
use Larmias\Contracts\ViewInterface;
use Larmias\View\View;

class ViewServiceProvider implements ServiceProviderInterface
{
    public function __construct(protected ContainerInterface $container)
    {

    }

    public function register(): void
    {
        $this->container->bind(ViewInterface::class, View::class);
    }

    public function boot(): void
    {
        // TODO: Implement boot() method.
    }
}