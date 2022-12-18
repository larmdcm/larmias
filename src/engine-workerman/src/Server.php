<?php

declare(strict_types=1);

namespace Larmias\Engine\WorkerMan;

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

        $config = $this->workerConfig->getSettings();
        $config['listen'] = sprintf('%s://%s:%d', $this->protocol, $this->workerConfig->getHost(), $this->workerConfig->getPort());

        $this->server = $this->makeWorker($config);
    }
}