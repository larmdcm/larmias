<?php

declare(strict_types=1);

namespace Larmias\Client\Pool;

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\Pool\ConnectionInterface;
use Larmias\Pool\Pool;
use Throwable;

class TcpClientPool extends Pool
{
    /**
     * @param ContainerInterface $container
     * @param array $options
     * @param array $config
     * @throws Throwable
     */
    public function __construct(ContainerInterface $container, array $options = [], protected array $config = [])
    {
        parent::__construct($container, $options);
    }

    /**
     * @return ConnectionInterface
     */
    public function createConnection(): ConnectionInterface
    {
        return new TcpClientConnection($this->config);
    }
}