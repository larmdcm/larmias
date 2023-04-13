<?php

declare(strict_types=1);

namespace Larmias\AsyncQueue\Providers;

use Larmias\Contracts\ApplicationInterface;
use Larmias\Contracts\VendorPublishInterface;
use Larmias\Contracts\ServiceProviderInterface;

class QueueServiceProvider implements ServiceProviderInterface
{
    /**
     * @param ApplicationInterface $app
     * @param VendorPublishInterface $vendorPublish
     */
    public function __construct(protected ApplicationInterface $app, protected VendorPublishInterface $vendorPublish)
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
        $this->vendorPublish->publishes(static::class, [
            __DIR__ . '/../../publish/async_queue.php' => $this->app->getConfigPath() . 'async_queue.php',
        ]);
    }
}