<?php

declare(strict_types=1);

namespace Larmias\Task\Providers;

use Larmias\Contracts\TaskExecutorInterface as BaseTaskExecutorInterface;
use Larmias\SharedMemory\Contracts\CommandExecutorInterface;
use Larmias\Task\Command\TaskCommand;
use Larmias\Task\Contracts\TaskExecutorInterface;
use Larmias\Task\TaskExecutor;
use Larmias\Framework\ServiceProvider;

class TaskServiceProvider extends ServiceProvider
{
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
        $this->publishes(static::class, [
            __DIR__ . '/../../publish/task.php' => $this->app->getConfigPath() . DIRECTORY_SEPARATOR . 'task.php',
        ]);

        /** @var CommandExecutorInterface $executor */
        $executor = $this->container->get(CommandExecutorInterface::class);
        $executor->addCommand(TaskCommand::COMMAND_NAME, TaskCommand::class);
    }
}