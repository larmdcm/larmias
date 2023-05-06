<?php

declare(strict_types=1);

namespace Larmias\Http\CSRF\Providers;

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\ServiceProviderInterface;
use Larmias\Http\CSRF\Contracts\CsrfManagerInterface;
use Larmias\Http\CSRF\CsrfManager;

class CsrfServiceProvider implements ServiceProviderInterface
{
    public function __construct(protected ContainerInterface $container)
    {
    }

    public function register(): void
    {
        $this->container->bind(CsrfManagerInterface::class, CsrfManager::class);
    }

    public function boot(): void
    {
        // TODO: Implement boot() method.
    }
}