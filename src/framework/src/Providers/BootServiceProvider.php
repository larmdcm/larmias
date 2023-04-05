<?php

declare(strict_types=1);

namespace Larmias\Framework\Providers;

use Larmias\Event\ListenerProviderFactory;
use Larmias\Framework\ServiceProvider;
use Psr\EventDispatcher\ListenerProviderInterface;
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
    }

    /**
     * @return void
     * @throws \Throwable
     */
    public function boot(): void
    {
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