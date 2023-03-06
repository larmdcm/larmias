<?php

declare(strict_types=1);

namespace Larmias\Engine\WorkerMan;

use Larmias\Engine\Event;
use Larmias\Engine\Timer;
use Throwable;

class Process extends EngineWorker
{
    /**
     * @var Worker
     */
    protected Worker $worker;

    /**
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->worker = $this->makeWorker($this->getMakeWorkerConfig());
    }

    /**
     * @param Worker $worker
     * @return void
     */
    public function onWorkerStart(Worker $worker): void
    {
        try {
            parent::onWorkerStart($worker);
            if ($this->hasListen(Event::ON_WORKER)) {
                $processTickInterval = $this->getSettings('process_tick_interval', 1);
                Timer::tick($processTickInterval, function () {
                    $this->trigger(Event::ON_WORKER, [$this]);
                });
            }
        } catch (Throwable $e) {
            $this->exceptionHandler($e);
        }
    }

    /**
     * @return array
     */
    protected function getMakeWorkerConfig(): array
    {
        return $this->getSettings();
    }
}