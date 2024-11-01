<?php

declare(strict_types=1);

namespace Larmias\Crontab\Providers;

use Larmias\Crontab\Contracts\ExecutorInterface;
use Larmias\Crontab\Contracts\ParserInterface;
use Larmias\Crontab\Contracts\SchedulerInterface;
use Larmias\Crontab\Executor\WorkerExecutor;
use Larmias\Crontab\Parser;
use Larmias\Crontab\Scheduler;
use Larmias\Framework\ServiceProvider;

class CrontabServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->container->bindIf([
            ParserInterface::class => Parser::class,
            SchedulerInterface::class => Scheduler::class,
            ExecutorInterface::class => WorkerExecutor::class,
        ]);
    }

    /**
     * @return void
     * @throws \Throwable
     */
    public function boot(): void
    {
        $this->publishes(static::class, [
            __DIR__ . '/../../publish/crontab.php' => $this->app->getConfigPath() . DIRECTORY_SEPARATOR . 'crontab.php',
        ]);
    }
}