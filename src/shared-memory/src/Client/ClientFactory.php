<?php

declare(strict_types=1);

namespace Larmias\SharedMemory\Client;

use Larmias\Contracts\ConfigInterface;
use Larmias\Contracts\ContainerInterface;
use Larmias\SharedMemory\Client\Proxy\Connection as ProxyConnection;
use Throwable;

class ClientFactory
{
    /**
     * @var array
     */
    protected array $config = [];

    /**
     * @param ContainerInterface $container
     * @param ConfigInterface $config
     */
    public function __construct(protected ContainerInterface $container, ConfigInterface $config)
    {
        $this->config = $config->get('shared_memory.client', []);
    }

    /**
     * @return Connection
     */
    public function make(): Connection
    {
        return new Connection($this->config);
    }

    /**
     * @return ProxyConnection
     * @throws Throwable
     */
    public function makeProxy(): ProxyConnection
    {
        return new ProxyConnection($this->container, $this->config);
    }
}