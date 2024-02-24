<?php

declare(strict_types=1);

namespace Larmias\Engine\Bootstrap;

use Larmias\Engine\Contracts\WorkerInterface;
use Larmias\Engine\Event;
use Larmias\Engine\Events\WorkerStart;
use Larmias\Engine\Events\AfterWorkerStart;
use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\StdoutLoggerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

class WorkerStartCallback
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
     * WorkerStartCallback constructor.
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
    public function onWorkerStart(WorkerInterface $worker): void
    {
        $settings = $worker->getSettings();
        $logger = $settings['logger'] ?? true;
        $workerId = $worker->getWorkerId();
        $logger && $this->logger?->info("{$worker->getWorkerConfig()->getName()} Worker#{$workerId} started.");
        $this->eventDispatcher && $this->eventDispatcher->dispatch(new WorkerStart($workerId));
        $worker->trigger(Event::ON_WORKER_START, [$worker]);
        $this->eventDispatcher?->dispatch(new AfterWorkerStart($workerId));
    }
}