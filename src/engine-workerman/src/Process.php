<?php

declare(strict_types=1);

namespace Larmias\Engine\WorkerMan;

use Larmias\Engine\Event;
use Workerman\Worker as WorkerManWorker;
use Larmias\Engine\Timer;
use Throwable;

class Process extends Worker
{
    /** @var WorkerManWorker */
    protected WorkerManWorker $worker;

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
     * @param WorkerManWorker $worker
     * @return void
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function onWorkerStart(WorkerManWorker $worker): void
    {
        try {
            parent::onWorkerStart($worker);
            Timer::tick($this->workerConfig->getSettings()['process_handle_wait_time'] ?? 1,function () {
                $this->trigger(Event::ON_WORKER,[$this]);
            });
        } catch (Throwable $e) {
            $this->exceptionHandler($e);
        }
    }
}