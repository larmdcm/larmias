<?php

declare(strict_types=1);

namespace Larmias\Engine\Drivers\WorkerS;

use Larmias\Engine\Worker;
use Larmias\WorkerS\Server as WorkerServer;
use Larmias\WorkerS\Constants\Event as WorkerEvent;
use Larmias\Engine\Event;

class Server extends Worker
{
    /** @var WorkerServer */
    protected WorkerServer $server;

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
        $this->server = new WorkerServer(
            sprintf('%s://%s:%d', $this->protocol, $this->workerConfig->getHost(), $this->workerConfig->getPort())
        );
        $this->server->setName($this->workerConfig->getName());
        $this->server->setConfig($this->workerConfig->getSettings());

        $this->server->on(WorkerEvent::ON_WORKER_START, function ($worker) {
            $this->trigger(Event::ON_WORKER_START, [$worker, $this]);
        });
    }
}