<?php

declare(strict_types=1);

namespace Larmias\Pool;

use Larmias\Contracts\ContainerInterface;
use Larmias\Pool\Contracts\ConnectionInterface;
use Larmias\Pool\Contracts\PoolInterface;
use Larmias\Pool\Contracts\PoolOptionInterface;
use Larmias\Engine\Coroutine;
use RuntimeException;
use function time;

abstract class Pool implements PoolInterface
{
    /**
     * @var Channel
     */
    protected Channel $channel;

    /**
     * @var PoolOptionInterface
     */
    protected PoolOptionInterface $option;

    /**
     * @var int
     */
    protected int $connectionCount = 0;

    /**
     * @param ContainerInterface $container
     * @param array $options
     */
    public function __construct(protected ContainerInterface $container, array $options = [])
    {
        $this->initOption($options);
        $this->initialize();
    }

    /**
     * @return ConnectionInterface
     */
    public function get(): ConnectionInterface
    {
        if ($this->channel->isEmpty()) {
            if ($this->connectionCount < $this->option->getMaxActive()) {
                return $this->getConnection();
            }
        }
        $connection = $this->channel->pop($this->option->getWaitTimeout());
        if (!$connection) {
            throw new RuntimeException('Connection pool exhausted. Cannot establish new connection before wait_timeout.');
        }
        return $connection;
    }

    /**
     * @param ConnectionInterface $connection
     * @return bool
     */
    public function release(ConnectionInterface $connection): bool
    {
        if ($this->channel->isFull()) {
            $this->disConnection($connection);
            return false;
        }
        $connection->setLastActiveTime(time());
        if (!$this->channel->push($connection)) {
            $this->disConnection($connection);
        }
        return true;
    }

    /**
     * 获取创建的连接数
     * @return int
     */
    public function getConnectionCount(): int
    {
        return $this->connectionCount;
    }

    /**
     * 获取空闲连接数
     * @return int
     */
    public function getIdleCount(): int
    {
        return $this->channel->length();
    }

    /**
     * @return void
     */
    protected function initialize(): void
    {
        $this->channel = new Channel($this->option->getMaxActive());
        Coroutine::create(function () {
            $maxActive = $this->option->getMaxActive();
            for ($i = 0; $i < $maxActive; $i++) {
                $connection = $this->getConnection();
                if (!$this->channel->push($connection)) {
                    $this->disConnection($connection);
                }
            }
        });
    }

    /**
     * @param array $options
     * @return void
     */
    protected function initOption(array $options = []): void
    {
        /** @var PoolOptionInterface $option */
        $option = $this->container->make(PoolOption::class, [
            'minActive' => $options['min_active'] ?? 1,
            'maxActive' => $options['max_active'] ?? 10,
            'maxLifetime' => $options['max_lifetime'] ?? -1,
            'maxIdleTime' => $options['max_idle_time'] ?? 60.0,
            'connectTimeout' => $options['connect_timeout'] ?? 10.0,
            'waitTimeout' => $options['wait_timeout'] ?? 3.0,
        ], true);
        $this->option = $option;
    }

    /**
     * @return PoolOptionInterface
     */
    public function getPoolOption(): PoolOptionInterface
    {
        return $this->option;
    }

    /**
     * @return ConnectionInterface
     */
    protected function getConnection(): ConnectionInterface
    {
        $connection = $this->createConnection();
        $connection->setLastActiveTime(time());
        $this->connectionCount++;
        return $connection;
    }

    /**
     * @param ConnectionInterface $connection
     * @return void
     */
    protected function disConnection(ConnectionInterface $connection): void
    {
        $this->connectionCount--;
    }
}