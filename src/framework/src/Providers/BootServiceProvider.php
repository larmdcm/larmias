<?php

declare(strict_types=1);

namespace Larmias\Framework\Providers;

use Larmias\Event\ListenerProviderFactory;
use Larmias\Framework\ServiceProvider;
use Psr\EventDispatcher\ListenerProviderInterface;
use Larmias\Contracts\VendorPublishInterface;
use function Larmias\Framework\config;
use function is_int;
use function is_string;

class BootServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->commands([
            \Larmias\Framework\Commands\Start::class,
            \Larmias\Framework\Commands\Stop::class,
            \Larmias\Framework\Commands\Restart::class,
            \Larmias\Framework\Commands\Reload::class,
            \Larmias\Framework\Commands\VendorPublish::class,
        ]);
    }

    /**
     * @return void
     * @throws \Throwable
     */
    public function boot(): void
    {
        $this->app->getContainer()->get(VendorPublishInterface::class)->publishes(static::class, [
            __DIR__ . '/../../publish/app.php' => $this->app->getConfigPath() . 'app.php',
        ]);
        $this->listeners();
    }

    /**
     * @return void
     * @throws \Throwable
     */
    protected function listeners(): void
    {
        $listeners = config('listeners', []);
        $provider = $this->app->getContainer()->get(ListenerProviderInterface::class);
        foreach ($listeners as $listener => $priority) {
            if (is_int($listener)) {
                $listener = $priority;
                $priority = 1;
            }
            if (is_string($listener)) {
                ListenerProviderFactory::register($provider, $this->app->getContainer(), $listener, $priority);
            }
        }
    }
}