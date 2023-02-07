<?php

declare(strict_types=1);

namespace Larmias\Engine\WorkerMan;

class Server extends EngineWorker
{
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