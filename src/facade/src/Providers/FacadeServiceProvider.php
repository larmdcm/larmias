<?php

declare(strict_types=1);

namespace Larmias\Facade\Providers;

use Larmias\Contracts\ServiceProviderInterface;
use Larmias\Facade\AbstractFacade;
use Larmias\Contracts\ContainerInterface;

class FacadeServiceProvider implements ServiceProviderInterface
{
    /**
     * @param ContainerInterface $container
     */
    public function __construct(protected ContainerInterface $container)
    {
    }

    public function register(): void
    {
        AbstractFacade::setContainer($this->container);
    }

    public function boot(): void
    {
    }
}