<?php

declare(strict_types=1);

namespace Larmias\Engine\WorkerMan;

use Larmias\Engine\Worker;
use Workerman\Worker as WorkerManWorker;

class Server extends Worker
{
    /** @var WorkerManWorker */
    protected WorkerManWorker $server;

    /**
     * @var string
     */
    protected string $protocol = 'tcp';

    /**
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->server = new WorkerManWorker(
            sprintf('%s://%s:%d', $this->protocol, $this->workerConfig->getHost(), $this->workerConfig->getPort())
        );

        $this->server->count = $this->workerConfig->getSettings()['worker_num'] ?? 1;

        $this->server->onWorkerStart = function ($worker) {
            $this->onWorkerStart($worker->id);
        };
    }
}