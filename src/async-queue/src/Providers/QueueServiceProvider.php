<?php

declare(strict_types=1);

namespace Larmias\AsyncQueue\Providers;

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\ApplicationInterface;
use Larmias\Contracts\VendorPublishInterface;
use Larmias\Contracts\ServiceProviderInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class QueueServiceProvider implements ServiceProviderInterface
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
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function boot(): void
    {
        if ($this->container->has(ApplicationInterface::class) && $this->container->has(VendorPublishInterface::class)) {
            $app = $this->container->get(ApplicationInterface::class);
            $this->container->get(VendorPublishInterface::class)->publishes(static::class, [
                __DIR__ . '/../../publish/async_queue.php' => $app->getConfigPath() . 'async_queue.php',
            ]);
        }
    }
}