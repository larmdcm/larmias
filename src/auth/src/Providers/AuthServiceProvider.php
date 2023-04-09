<?php

declare(strict_types=1);

namespace Larmias\Auth\Providers;

use Larmias\Auth\Facade\Auth;
use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\ContextInterface;
use Larmias\Contracts\ServiceProviderInterface;

class AuthServiceProvider implements ServiceProviderInterface
{
    /**
     * @param ContainerInterface $container
     */
    public function __construct(protected ContainerInterface $container)
    {
    }

    /**
     * @return void
     */
    public function register(): void
    {
    }

    /**
     * @return void
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function boot(): void
    {
        Auth::setContext($this->container->get(ContextInterface::class));
    }
}