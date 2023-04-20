<?php

declare(strict_types=1);

namespace Larmias\Redis\Providers;

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\Redis\RedisFactoryInterface;
use Larmias\Contracts\ServiceProviderInterface;
use Larmias\Redis\RedisFactory;
use Larmias\Contracts\ApplicationInterface;
use Larmias\Contracts\VendorPublishInterface;

class RedisServiceProvider implements ServiceProviderInterface
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
        $this->container->bindIf(RedisFactoryInterface::class, RedisFactory::class);
    }

    /**
     * @return void
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function boot(): void
    {
        if ($this->container->has(ApplicationInterface::class) && $this->container->has(VendorPublishInterface::class)) {
            $app = $this->container->get(ApplicationInterface::class);
            $this->container->get(VendorPublishInterface::class)->publishes(static::class, [
                __DIR__ . '/../../publish/redis.php' => $app->getConfigPath() . 'redis.php',
            ]);
        }
    }
}