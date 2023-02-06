<?php

declare(strict_types=1);

namespace Larmias\Session\Providers;

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\ServiceProviderInterface;
use Larmias\Contracts\SessionInterface;
use Larmias\Session\Session;

class SessionServiceProvider implements ServiceProviderInterface
{
    public function __construct(protected ContainerInterface $container)
    {

    }

    public function register(): void
    {
        $this->container->bind(SessionInterface::class, Session::class);
    }

    public function boot(): void
    {
        // TODO: Implement boot() method.
    }
}