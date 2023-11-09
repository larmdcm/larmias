<?php

declare(strict_types=1);

namespace Larmias\AsyncQueue;

use Larmias\AsyncQueue\Contracts\QueueDriverInterface;
use Larmias\AsyncQueue\Contracts\QueueInterface;
use Larmias\AsyncQueue\Message\Message;
use Larmias\Contracts\ConfigInterface;
use Larmias\Contracts\ContainerInterface;
use Larmias\AsyncQueue\Contracts\MessageInterface;
use function is_null;

class Queue implements QueueInterface
{
    /**
     * @var QueueDriverInterface[]
     */
    protected array $drivers = [];

    /**
     * @param ContainerInterface $container
     * @param ConfigInterface $config
     */
    public function __construct(protected ContainerInterface $container, protected ConfigInterface $config)
    {
    }

    /**
     * @param string $handler
     * @param array $data
     * @param int $delay
     * @return MessageInterface
     */
    public function push(string $handler, array $data = [], int $delay = 0): MessageInterface
    {
        $message = new Message($handler, $data);
        return $this->driver()->push($message, $delay);
    }

    /**
     * @param string|null $name
     * @return QueueDriverInterface
     */
    public function driver(?string $name = null): QueueDriverInterface
    {
        $name = $name ?: $this->getConfig('default');
        if (isset($this->drivers[$name])) {
            return $this->drivers[$name];
        }
        $config = $this->getConfig('queues.' . $name);
        /** @var QueueDriverInterface $store */
        $store = $this->container->make($config['driver'], ['config' => $config]);
        return $this->drivers[$name] = $store;
    }

    /**
     * 获取配置.
     * @param string|null $name
     * @param mixed $default
     * @return mixed
     */
    public function getConfig(?string $name = null, mixed $default = null): mixed
    {
        if (is_null($name)) {
            return $this->config->get('async_queue');
        }
        return $this->config->get('async_queue.' . $name, $default);
    }
}