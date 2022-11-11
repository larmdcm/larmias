<?php

declare(strict_types=1);

namespace Larmias\Engine\Drivers\WorkerS;

use Larmias\Engine\Worker;
use Larmias\WorkerS\Server as WorkerServer;

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
            sprintf('%s://%s:%d',$this->protocol,$this->workerConfig->getHost(),$this->workerConfig->getPort())
        );
        $this->server->setName($this->workerConfig->getName());
        $this->server->setConfig($this->workerConfig->getSettings());
    }
}