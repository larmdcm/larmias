<?php

declare(strict_types=1);

namespace Larmias\Engine\Bootstrap;

use Larmias\Engine\Contracts\WorkerInterface;
use Larmias\Engine\Event;
use Larmias\Contracts\ContainerInterface;
use Larmias\Engine\Events\WorkerStop;
use Psr\EventDispatcher\EventDispatcherInterface;
use Larmias\Contracts\StdoutLoggerInterface;

class WorkerStopCallback
{
    /**
     * @var EventDispatcherInterface|null
     */
    protected ?EventDispatcherInterface $eventDispatcher = null;

    /**
     * @var StdoutLoggerInterface|null
     */
    protected ?StdoutLoggerInterface $logger = null;

    /**
     * WorkerStopCallback constructor.
     *
     * @param ContainerInterface $container
     * @throws \Throwable
     */
    public function __construct(protected ContainerInterface $container)
    {
        if ($this->container->has(EventDispatcherInterface::class)) {
            $this->eventDispatcher = $this->container->get(EventDispatcherInterface::class);
        }

        if ($this->container->has(StdoutLoggerInterface::class)) {
            $this->logger = $this->container->get(StdoutLoggerInterface::class);
        }
    }

    /**
     * @param WorkerInterface $worker
     * @return void
     */
    public function onWorkerStop(WorkerInterface $worker): void
    {
        $settings = $worker->getSettings();
        $logger = $settings['logger'] ?? true;
        $workerId = $worker->getWorkerId();
        $logger && $this->logger?->info("{$worker->getWorkerConfig()->getName()} Worker#{$workerId} stop.");
        $this->eventDispatcher && $this->eventDispatcher->dispatch(new WorkerStop($workerId));
        $worker->trigger(Event::ON_WORKER_STOP, [$worker]);
    }
}