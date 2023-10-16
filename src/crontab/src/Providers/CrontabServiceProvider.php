<?php

declare(strict_types=1);

namespace Larmias\Crontab\Providers;

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\ServiceProviderInterface;
use Larmias\Contracts\ApplicationInterface;
use Larmias\Contracts\VendorPublishInterface;
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
        if ($this->container->has(ApplicationInterface::class) && $this->container->has(VendorPublishInterface::class)) {
            $app = $this->container->get(ApplicationInterface::class);
            $this->container->get(VendorPublishInterface::class)->publishes(static::class, [
                __DIR__ . '/../../publish/crontab.php' => $app->getConfigPath() . 'crontab.php',
            ]);
        }
    }
}