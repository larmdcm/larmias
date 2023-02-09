<?php

declare(strict_types=1);

namespace Larmias\Task\Providers;

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\ServiceProviderInterface;
use Larmias\Contracts\TaskExecutorInterface as BaseTaskExecutorInterface;
use Larmias\SharedMemory\Contracts\CommandExecutorInterface;
use Larmias\Task\Command\TaskCommand;
use Larmias\Task\Contracts\TaskExecutorInterface;
use Larmias\Task\TaskExecutor;

class TaskServiceProvider implements ServiceProviderInterface
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
            BaseTaskExecutorInterface::class => TaskExecutor::class,
            TaskExecutorInterface::class => TaskExecutor::class,
        ]);
    }

    /**
     * @return void
     * @throws \Throwable
     */
    public function boot(): void
    {
        /** @var CommandExecutorInterface $executor */
        $executor = $this->container->get(CommandExecutorInterface::class);
        $executor->addCommand(TaskCommand::COMMAND_NAME, TaskCommand::class);
    }
}