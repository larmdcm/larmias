<?php

declare(strict_types=1);

namespace Larmias\Process\Providers;

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\ServiceProviderInterface;

class ProcessServiceProvider implements ServiceProviderInterface
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
        // TODO: Implement register() method.
    }

    /**
     * @return void
     */
    public function boot(): void
    {
        // TODO: Implement boot() method.
    }
}