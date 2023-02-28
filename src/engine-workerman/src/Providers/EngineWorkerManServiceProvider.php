<?php

declare(strict_types=1);

namespace Larmias\Engine\WorkerMan\Providers;

use Larmias\Contracts\ServiceDiscoverInterface;
use Larmias\Contracts\ServiceProviderInterface;
use Larmias\Engine\WorkerMan\Commands\Status;

class EngineWorkerManServiceProvider implements ServiceProviderInterface
{
    /**
     * @param ServiceDiscoverInterface $serviceDiscover
     */
    public function __construct(protected ServiceDiscoverInterface $serviceDiscover)
    {
    }

    public function register(): void
    {
        $this->serviceDiscover->commands([
            Status::class,
        ]);
    }

    public function boot(): void
    {
        // TODO: Implement boot() method.
    }
}