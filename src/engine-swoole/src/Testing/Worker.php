<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole\Testing;

use Larmias\Engine\Contracts\WorkerInterface;
use Larmias\Engine\Swoole\Worker as BaseWorker;
use Throwable;

class Worker extends BaseWorker
{
    public function __construct(protected WorkerInterface $worker)
    {
        parent::__construct($worker->getContainer(), $worker->getKernel(), $worker->getWorkerConfig());
    }

    /**
     * @return void
     */
    public function process(): void
    {
        if (method_exists($this->worker, 'process')) {
            $this->worker->process(false);
        }
    }

    /**
     * @param Throwable $e
     * @return void
     */
    public function handleException(Throwable $e): void
    {
        $this->printException($e);
    }
}