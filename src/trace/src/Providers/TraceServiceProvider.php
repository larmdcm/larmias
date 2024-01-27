<?php

declare(strict_types=1);

namespace Larmias\Trace\Providers;

use Larmias\Trace\Contracts\TraceContextInterface;
use Larmias\Trace\Contracts\TraceInterface;
use Larmias\Trace\Listeners\DatabaseQueryExecutedListener;
use Larmias\Trace\Trace;
use Larmias\Trace\TraceContext;
use Larmias\Framework\ServiceProvider;

class TraceServiceProvider extends ServiceProvider
{
    /**
     * @return void
     * @throws \Throwable
     */
    public function register(): void
    {
        $this->container->bindIf([
            TraceInterface::class => Trace::class,
            TraceContextInterface::class => TraceContext::class,
        ]);
    }

    /**
     * @return void
     * @throws \Throwable
     */
    public function boot(): void
    {
        if (!$this->config->has('trace')) {
            return;
        }

        $this->setListener();
        $this->publishes(static::class, [
            __DIR__ . '/../../publish/trace.php' => $this->app->getConfigPath() . 'trace.php',
        ]);
    }

    /**
     * @return void
     * @throws \Throwable
     */
    protected function setListener(): void
    {
        $listeners = [
            DatabaseQueryExecutedListener::class,
        ];

        $this->listener($listeners);
    }
}