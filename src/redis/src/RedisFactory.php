<?php

declare(strict_types=1);

namespace Larmias\Redis;

use Larmias\Contracts\ConfigInterface;
use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\Redis\ConnectionInterface;
use Larmias\Contracts\Redis\RedisFactoryInterface;
use Throwable;

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
     * @param string|null $name
     * @return ConnectionInterface
     * @throws Throwable
     */
    public function get(?string $name = null): ConnectionInterface
    {
        if (is_null($name)) {
            $name = $this->config->get('default', 'default');
        }

        if (!isset($this->proxies[$name])) {
            $config = $this->config->get('redis.connections.' . $name, []);
            $config['name'] = $name;
            $this->proxies[$name] = new RedisProxy($this->container, $config);
        }
        return $this->proxies[$name];
    }
}