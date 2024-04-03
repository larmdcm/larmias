<?php

declare(strict_types=1);

namespace Larmias\SharedMemory\Providers;

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\ServiceProviderInterface;
use Larmias\SharedMemory\Auth;
use Larmias\SharedMemory\CommandExecutor;
use Larmias\SharedMemory\Contracts\AuthInterface;
use Larmias\SharedMemory\Contracts\CommandExecutorInterface;
use Larmias\SharedMemory\Contracts\LockerInterface;
use Larmias\SharedMemory\Contracts\LoggerInterface;
use Larmias\SharedMemory\Locker;
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
        $this->container->bindIf([
            CommandExecutorInterface::class => CommandExecutor::class,
            AuthInterface::class => Auth::class,
            LoggerInterface::class => Logger::class,
            LockerInterface::class => Locker::class,
        ]);
    }

    /**
     * @return void
     * @throws \Throwable
     */
    public function boot(): void
    {
    }
}