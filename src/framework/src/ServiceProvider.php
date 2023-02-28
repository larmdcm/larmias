<?php

declare(strict_types=1);

namespace Larmias\Framework;

use Larmias\Contracts\ApplicationInterface;
use Larmias\Contracts\ServiceProviderInterface;
use Larmias\Contracts\ServiceDiscoverInterface;

abstract class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @param ApplicationInterface $app
     * @param ServiceDiscoverInterface $serviceDiscover
     */
    public function __construct(protected ApplicationInterface $app, protected ServiceDiscoverInterface $serviceDiscover)
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
     */
    public function boot(): void
    {
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
     * @param string|array $commands
     * @return void
     */
    public function commands(string|array $commands): void
    {
        $this->serviceDiscover->commands($commands);
    }
}