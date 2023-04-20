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
use Larmias\Contracts\ApplicationInterface;
use Larmias\Contracts\VendorPublishInterface;

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
        $this->container->bindIf([
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
        if ($this->container->has(ApplicationInterface::class) && $this->container->has(VendorPublishInterface::class)) {
            $app = $this->container->get(ApplicationInterface::class);
            $this->container->get(VendorPublishInterface::class)->publishes(static::class, [
                __DIR__ . '/../../publish/task.php' => $app->getConfigPath() . 'task.php',
            ]);
        }

        /** @var CommandExecutorInterface $executor */
        $executor = $this->container->get(CommandExecutorInterface::class);
        $executor->addCommand(TaskCommand::COMMAND_NAME, TaskCommand::class);
    }
}