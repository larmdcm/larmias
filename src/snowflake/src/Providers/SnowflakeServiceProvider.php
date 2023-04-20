<?php

declare(strict_types=1);

namespace Larmias\Snowflake\Providers;

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\ServiceProviderInterface;
use Larmias\Snowflake\Contracts\IdGeneratorInterface;
use Larmias\Snowflake\IdGenerator;
use Larmias\Contracts\ApplicationInterface;
use Larmias\Contracts\VendorPublishInterface;

class SnowflakeServiceProvider implements ServiceProviderInterface
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
        $this->container->bindIf(IdGeneratorInterface::class, IdGenerator::class);
    }

    /**
     * @return void
     */
    public function boot(): void
    {
        if ($this->container->has(ApplicationInterface::class) && $this->container->has(VendorPublishInterface::class)) {
            $app = $this->container->get(ApplicationInterface::class);
            $this->container->get(VendorPublishInterface::class)->publishes(static::class, [
                __DIR__ . '/../../publish/snowflake.php' => $app->getConfigPath() . 'snowflake.php',
            ]);
        }
    }
}