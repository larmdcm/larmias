<?php

declare(strict_types=1);

namespace Larmias\Redis;

use Larmias\Contracts\ConfigInterface;
use Larmias\Contracts\Redis\ConnectionInterface;
use Larmias\Contracts\Redis\RedisFactoryInterface;

class RedisFactory implements RedisFactoryInterface
{
    /**
     * @var ConnectionInterface[]
     */
    protected array $connections = [];

    public function __construct(protected ConfigInterface $config)
    {
    }

    public function get(string $name = 'default'): ConnectionInterface
    {
        if (!isset($this->connections[$name])) {
            $config = $this->config->get('redis.' . $name, []);
            $this->connections[$name] = new Connection($config);
        }
        return $this->connections[$name];
    }
}