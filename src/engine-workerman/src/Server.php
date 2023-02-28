<?php

declare(strict_types=1);

namespace Larmias\Engine\WorkerMan;

use function sprintf;

class Server extends EngineWorker
{
    /**
     * @var Worker
     */
    protected Worker $server;

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

        $config = $this->getSettings();
        $config['listen'] = sprintf('%s://%s:%d', $this->protocol, $this->workerConfig->getHost(), $this->workerConfig->getPort());

        $this->server = $this->makeWorker($config);
    }

    /**
     * @return string
     */
    public function getProtocol(): string
    {
        return $this->protocol;
    }
}