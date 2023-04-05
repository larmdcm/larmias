<?php

declare(strict_types=1);

namespace Larmias\FileWatcher\Providers;

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\FileWatcherInterface;
use Larmias\Contracts\ServiceProviderInterface;
use Larmias\FileWatcher\Watcher;

class FileWatcherServiceProvider implements ServiceProviderInterface
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
        $this->container->bindIf(FileWatcherInterface::class, Watcher::class);
    }

    /**
     * @return void
     */
    public function boot(): void
    {
    }
}