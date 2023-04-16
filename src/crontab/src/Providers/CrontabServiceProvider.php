<?php

declare(strict_types=1);

namespace Larmias\Crontab\Providers;

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\ServiceProviderInterface;
use Larmias\Crontab\Contracts\ExecutorInterface;
use Larmias\Crontab\Contracts\ParserInterface;
use Larmias\Crontab\Contracts\SchedulerInterface;
use Larmias\Crontab\Executor\WorkerExecutor;
use Larmias\Crontab\Parser;
use Larmias\Crontab\Scheduler;

class CrontabServiceProvider implements ServiceProviderInterface
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
        $this->container->bind([
            ParserInterface::class => Parser::class,
            SchedulerInterface::class => Scheduler::class,
            ExecutorInterface::class => WorkerExecutor::class,
        ]);
    }

    /**
     * @return void
     */
    public function boot(): void
    {
    }
}