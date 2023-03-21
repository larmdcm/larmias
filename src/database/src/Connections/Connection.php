<?php

namespace Larmias\Database\Connections;

use Larmias\Pool\Connection as PoolConnection;
use Larmias\Database\Contracts\ConnectionInterface;
use Larmias\Contracts\Pool\ConnectionInterface as PoolConnectionInterface;
use function array_merge;

abstract class Connection extends PoolConnection implements ConnectionInterface, PoolConnectionInterface
{
    /**
     * @var array
     */
    protected array $config = [];

    /**
     * @var string
     */
    protected string $lastSql = '';

    /**
     * @var array
     */
    protected array $lastBinds = [];

    /**
     * @var float
     */
    protected float $lastRunTime = 0.0;

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
        return $this->config[$name] ?? $default;
    }

    /**
     * @return string
     */
    public function getLastSql(): string
    {
        return $this->buildSql($this->lastSql, $this->lastBinds);
    }

    /**
     * @return float
     */
    public function getLastRunTime(): float
    {
        return $this->lastRunTime;
    }
}