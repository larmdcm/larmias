<?php

declare(strict_types=1);

namespace Larmias\SharedMemory\Providers;

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\ServiceProviderInterface;
use Larmias\SharedMemory\Auth;
use Larmias\SharedMemory\CommandExecutor;
use Larmias\SharedMemory\Contracts\AuthInterface;
use Larmias\SharedMemory\Contracts\CommandExecutorInterface;
use Larmias\SharedMemory\Contracts\LoggerInterface;
use Larmias\SharedMemory\Logger;

class SharedMemoryServiceProvider implements ServiceProviderInterface
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
        $this->container->bind([
            CommandExecutorInterface::class => CommandExecutor::class,
            AuthInterface::class => Auth::class,
            LoggerInterface::class => Logger::class,
        ]);
    }

    /**
     * @return void
     */
    public function boot(): void
    {
        // TODO: Implement boot() method.
    }
}