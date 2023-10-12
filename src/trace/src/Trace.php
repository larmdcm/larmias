<?php

declare(strict_types=1);

namespace Larmias\Trace;

use Larmias\Contracts\ContainerInterface;
use Larmias\Trace\Contracts\CollectorInterface;
use Larmias\Trace\Contracts\TraceInterface;
use RuntimeException;

class Trace implements TraceInterface
{
    /**
     * @var array
     */
    protected array $collectors = [];

    /**
     * @param ContainerInterface $container
     * @param array $config
     */
    public function __construct(protected ContainerInterface $container, protected array $config = [])
    {
    }

    /**
     * 获取指定收集器
     * @param string $name
     * @return CollectorInterface
     */
    public function getCollector(string $name): CollectorInterface
    {
        if (!isset($this->collectors[$name])) {
            if (!isset($this->config['collectors'][$name])) {
                throw new RuntimeException('collectors not exists:' . $name);
            }
            $this->collectors[$name] = $this->container->make($this->config['collectors'][$name], [], true);
        }
        return $this->collectors[$name];
    }

    /**
     * 获取全部收集器
     * @return CollectorInterface[]
     */
    public function getAllCollector(): array
    {
        return $this->collectors;
    }
}