<?php

declare(strict_types=1);

namespace Larmias\AsyncQueue\Providers;

use Larmias\AsyncQueue\Contracts\QueueInterface;
use Larmias\AsyncQueue\Queue;
use Larmias\Framework\ServiceProvider;

class QueueServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->container->bindIf([
            QueueInterface::class => Queue::class,
        ]);
    }

    /**
     * @return void
     * @throws \Throwable
     */
    public function boot(): void
    {
        $this->publishes(static::class, [
            __DIR__ . '/../../publish/async_queue.php' => $this->app->getConfigPath() . 'async_queue.php',
        ]);
    }
}