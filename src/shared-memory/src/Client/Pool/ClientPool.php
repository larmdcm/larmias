<?php

declare(strict_types=1);

namespace Larmias\SharedMemory\Client\Pool;

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\Pool\ConnectionInterface;
use Larmias\Pool\Pool;
use Throwable;

class ClientPool extends Pool
{
    /**
     * @param ContainerInterface $container
     * @param array $options
     * @param array $config
     * @throws Throwable
     */
    public function __construct(protected ContainerInterface $container, array $options = [], protected array $config = [])
    {
        parent::__construct($this->container, $options);
    }

    /**
     * @return ConnectionInterface
     */
    public function createConnection(): ConnectionInterface
    {
        return new ClientConnection($this->config);
    }
}