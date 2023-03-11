<?php

declare(strict_types=1);

namespace Larmias\Redis;

use Larmias\Contracts\ConfigInterface;
use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\Redis\ConnectionInterface;
use Larmias\Contracts\Redis\RedisFactoryInterface;

class RedisFactory implements RedisFactoryInterface
{
    /**
     * @var ConnectionInterface[]
     */
    protected array $proxies = [];

    /**
     * @param ContainerInterface $container
     * @param ConfigInterface $config
     */
    public function __construct(protected ContainerInterface $container, protected ConfigInterface $config)
    {
    }

    /**
     * @param string $name
     * @return ConnectionInterface
     */
    public function get(string $name = 'default'): ConnectionInterface
    {
        if (!isset($this->proxies[$name])) {
            $config = $this->config->get('redis.' . $name, []);
            $config['name'] = $name;
            $this->proxies[$name] = new RedisProxy($this->container, $config);
        }
        return $this->proxies[$name];
    }
}