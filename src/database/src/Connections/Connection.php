<?php

declare(strict_types=1);

namespace Larmias\Database\Connections;

use Larmias\Database\Contracts\ConnectionInterface;
use Larmias\Pool\Connection as PoolConnection;
use Psr\EventDispatcher\EventDispatcherInterface;
use function array_merge;
use function Larmias\Collection\data_get;

abstract class Connection extends PoolConnection implements ConnectionInterface
{
    /**
     * @var array
     */
    protected array $config = [];

    /**
     * @var string
     */
    protected string $executeSql = '';

    /**
     * @var array
     */
    protected array $executeBindings = [];

    /**
     * @var float
     */
    protected float $executeTime = 0.0;

    /**
     * @var EventDispatcherInterface
     */
    protected EventDispatcherInterface $eventDispatcher;

    /**
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * 获取配置
     * @param string|null $name
     * @param mixed|null $default
     * @return mixed
     */
    public function getConfig(?string $name = null, mixed $default = null): mixed
    {
        if ($name === null) {
            return $this->config;
        }
        return data_get($this->config, $name, $default);
    }

    /**
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher(): EventDispatcherInterface
    {
        return $this->eventDispatcher;
    }

    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @return void
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }
}