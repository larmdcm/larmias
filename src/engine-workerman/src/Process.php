<?php

declare(strict_types=1);

namespace Larmias\Engine\WorkerMan;

use Larmias\Engine\Event;
use Larmias\Engine\Timer;
use Throwable;

class Process extends EngineWorker
{
    protected Worker $worker;

    /**
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $config = $this->workerConfig->getSettings();
        $this->worker = $this->makeWorker($config);
    }

    /**
     * @param Worker $worker
     * @return void
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function onWorkerStart(Worker $worker): void
    {
        try {
            parent::onWorkerStart($worker);
            $processTickTime = $this->workerConfig->getSettings()['process_tick_time'] ?? 1;
            if ($this->hasListen(Event::ON_WORKER)) {
                Timer::tick($processTickTime, function () {
                    $this->trigger(Event::ON_WORKER, [$this]);
                });
            }
        } catch (Throwable $e) {
            $this->exceptionHandler($e);
        }
    }
}