<?php

declare(strict_types=1);

namespace Larmias\Engine\WorkerMan\Providers;

use Larmias\Contracts\ServiceDiscoverInterface;
use Larmias\Contracts\ServiceProviderInterface;
use Larmias\Engine\WorkerMan\Commands\Status;

class WorkerManServiceProvider implements ServiceProviderInterface
{
    /**
     * @param ServiceDiscoverInterface|null $serviceDiscover
     */
    public function __construct(protected ?ServiceDiscoverInterface $serviceDiscover = null)
    {
    }

    public function register(): void
    {
        $this->serviceDiscover?->commands([
            Status::class,
        ]);
    }

    public function boot(): void
    {
        // TODO: Implement boot() method.
    }
}