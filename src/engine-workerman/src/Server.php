<?php

declare(strict_types=1);

namespace Larmias\Engine\WorkerMan;

use function sprintf;

class Server extends EngineWorker
{
    /**
     * @var string
     */
    protected string $protocol = 'tcp';

    /**
     * @return string
     */
    public function getProtocol(): string
    {
        return $this->protocol;
    }

    /**
     * @return array
     */
    public function getMakeWorkerConfig(): array
    {
        $config = $this->getSettings();
        $config['listen'] = sprintf('%s://%s:%d', $this->protocol, $this->workerConfig->getHost(), $this->workerConfig->getPort());

        return $config;
    }
}