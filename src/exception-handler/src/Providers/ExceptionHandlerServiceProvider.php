<?php

declare(strict_types=1);

namespace Larmias\ExceptionHandler\Providers;

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\ServiceProviderInterface;
use Larmias\Contracts\ApplicationInterface;
use Larmias\Contracts\VendorPublishInterface;
use Larmias\ExceptionHandler\Contracts\ExceptionHandlerDispatcherInterface;
use Larmias\ExceptionHandler\ExceptionHandlerDispatcher;

class ExceptionHandlerServiceProvider implements ServiceProviderInterface
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
        $this->container->bindIf(ExceptionHandlerDispatcherInterface::class, ExceptionHandlerDispatcher::class);
    }

    /**
     * @return void
     */
    public function boot(): void
    {
        if ($this->container->has(ApplicationInterface::class) && $this->container->has(VendorPublishInterface::class)) {
            $app = $this->container->get(ApplicationInterface::class);
            $this->container->get(VendorPublishInterface::class)->publishes(static::class, [
                __DIR__ . '/../../publish/exceptions.php' => $app->getConfigPath() . 'exceptions.php',
            ]);
        }
    }
}