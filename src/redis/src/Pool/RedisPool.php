<?php

declare(strict_types=1);

namespace Larmias\Redis\Pool;

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\Pool\ConnectionInterface;
use Larmias\Pool\Pool;

class RedisPool extends Pool
{
    /**
     * @param ContainerInterface $container
     * @param array $options
     * @param array $config
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
        return new RedisConnection($this->config);
    }
}