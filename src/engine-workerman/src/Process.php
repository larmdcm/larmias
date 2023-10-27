<?php

declare(strict_types=1);

namespace Larmias\Engine\WorkerMan;

use Larmias\Engine\WorkerMan\Contracts\WorkerInterface;

class Process extends EngineWorker implements WorkerInterface
{
    /**
     * @return void
     * @throws \Exception
     */
    public function process(): void
    {
        $worker = Worker::getProcessWorker();
        $this->onWorkerStart($worker);
        Worker::getEventLoop()->loop();
    }
}