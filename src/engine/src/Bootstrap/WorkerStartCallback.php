<?php

declare(strict_types=1);

namespace Larmias\Engine\Bootstrap;

use Larmias\Engine\Contracts\WorkerInterface;
use Larmias\Engine\Event;
use Larmias\Engine\Events\WorkerStart;
use Larmias\Engine\Events\AfterWorkerStart;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;

class WorkerStartCallback
{
    /**
     * @var \Psr\EventDispatcher\EventDispatcherInterface|null
     */
    protected ?EventDispatcherInterface $eventDispatcher = null;

    /**
     * @var \Psr\Log\LoggerInterface|null
     */
    protected ?LoggerInterface $logger = null;

    /**
     * WorkerStartCallback constructor.
     *
     * @param \Psr\Container\ContainerInterface $container
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __construct(protected ContainerInterface $container)
    {
        if ($this->container->has(EventDispatcherInterface::class)) {
            $this->eventDispatcher = $this->container->get(EventDispatcherInterface::class);
        }
        if ($this->container->has(LoggerInterface::class)) {
            $this->logger = $this->container->get(LoggerInterface::class);
        }
    }

    /**
     * @param \Larmias\Engine\Contracts\WorkerInterface $worker
     * @return void
     */
    public function onWorkerStart(WorkerInterface $worker): void
    {
        $workerId = $worker->getWorkerId();
        $this->logger && $this->logger->info("Worker#{$workerId} started.");
        $this->eventDispatcher && $this->eventDispatcher->dispatch(new WorkerStart($workerId));
        $worker->trigger(Event::ON_WORKER_START, [$worker]);
        $this->eventDispatcher && $this->eventDispatcher->dispatch(new AfterWorkerStart($workerId));
    }
}