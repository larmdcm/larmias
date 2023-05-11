<?php

declare(strict_types=1);

namespace Larmias\AsyncQueue;

use Larmias\AsyncQueue\Contracts\QueueDriverInterface;
use Larmias\AsyncQueue\Contracts\JobInterface;
use Larmias\AsyncQueue\Contracts\QueueInterface;
use Larmias\AsyncQueue\Message\Message;
use Larmias\Contracts\ConfigInterface;
use Larmias\Di\Container;
use function is_null;
use function session_create_id;

class Queue implements QueueInterface
{
    /**
     * @var QueueDriverInterface[]
     */
    protected array $drivers = [];

    /**
     * @param Container $container
     * @param ConfigInterface $config
     */
    public function __construct(protected Container $container, protected ConfigInterface $config)
    {
    }

    /**
     * @param JobInterface $job
     * @param array $data
     * @param float $delay
     * @return string
     * @throws \ReflectionException
     */
    public function push(JobInterface $job, array $data = [], float $delay = 0): string
    {
        $message = new Message($job, $data, session_create_id());
        return $this->driver()->push($message, $delay);
    }

    /**
     * @param string|null $name
     * @return QueueDriverInterface
     * @throws \ReflectionException
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