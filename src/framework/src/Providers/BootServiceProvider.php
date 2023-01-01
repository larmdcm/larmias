<?php

declare(strict_types=1);

namespace Larmias\Framework\Providers;

use Larmias\Event\ListenerProviderFactory;
use Larmias\Framework\ServiceProvider;
use Psr\EventDispatcher\ListenerProviderInterface;

use function Larmias\Framework\config;

class BootServiceProvider extends ServiceProvider
{
    /**
     * @return void
     * @throws \Throwable
     */
    public function register(): void
    {
        $this->listeners();
    }

    /**
     * @return void
     * @throws \Throwable
     */
    protected function listeners(): void
    {
        $listeners = config('listeners',[]);
        $provider = $this->app->get(ListenerProviderInterface::class);
        foreach ($listeners as $listener => $priority) {
            if (is_int($listener)) {
                $listener = $priority;
                $priority = 1;
            }
            if (is_string($listener)) {
                ListenerProviderFactory::register($provider, $this->app, $listener, $priority);
            }
        }
    }
}