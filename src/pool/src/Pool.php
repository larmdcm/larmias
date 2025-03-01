<?php

declare(strict_types=1);

namespace Larmias\Pool;

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\ContextInterface;
use Larmias\Contracts\Coroutine\ChannelFactoryInterface;
use Larmias\Contracts\Coroutine\CoroutineInterface;
use Larmias\Contracts\Pool\ConnectionInterface;
use Larmias\Contracts\Pool\PoolInterface;
use Larmias\Contracts\Pool\PoolOptionInterface;
use Larmias\Contracts\TimerInterface;
use RuntimeException;
use Throwable;
use function time;
use function intval;
use function array_merge;

abstract class Pool implements PoolInterface
{
    /**
     * @var Channel
     */
    protected Channel $channel;

    /**
     * @var PoolOptionInterface
     */
    protected PoolOptionInterface $poolOption;

    /**
     * @var ContextInterface
     */
    protected ContextInterface $context;

    /**
     * @var CoroutineInterface|null
     */
    protected ?CoroutineInterface $coroutine = null;

    /**
     * @var TimerInterface
     */
    protected TimerInterface $timer;

    /**
     * @var int
     */
    protected int $connectionCount = 0;

    /**
     * @var int
     */
    protected int $heartbeatId = 0;

    /**
     * @var bool
     */
    protected bool $closed = false;

    /**
     * @var array
     */
    protected array $options = [
        'min_active' => 1,
        'max_active' => 10,
        'wait_timeout' => 3.0,
        'max_idle_time' => 60.0,
        'max_lifetime' => 0.0,
        'heartbeat' => 0.0,
        'close_on_destruct' => false,
    ];

    /**
     * @param ContainerInterface $container
     * @param array $options
     * @throws Throwable
     */
    public function __construct(protected ContainerInterface $container, array $options = [])
    {
        $this->context = $this->container->get(ContextInterface::class);
        if ($this->isCoroutine()) {
            $this->coroutine = $this->container->get(CoroutineInterface::class);
        }
        $this->timer = $this->container->get(TimerInterface::class);
        $this->options = array_merge($this->options, $options);
        $this->initPoolOption();
        $this->initialize();
    }

    /**
     * @return ConnectionInterface
     */
    public function get(): ConnectionInterface
    {
        if ($this->channel->isEmpty()) {
            if ($this->connectionCount < $this->poolOption->getMaxActive()) {
                return $this->getConnection();
            }
        }
        $connection = $this->channel->pop($this->poolOption->getWaitTimeout());
        if (!$connection) {
            throw new RuntimeException('Connection pool exhausted. Cannot establish new connection before wait timeout.');
        }
        if ($connection->isConnected()) {
            $connection->reset();
        } else {
            $this->disConnection($connection);
            $connection = $this->getConnection();
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
        } else {
            $connection->setLastActiveTime(time());
            if (!$this->channel->push($connection)) {
                $this->disConnection($connection);
            }
        }
        return true;
    }

    /**
     * @return bool
     */
    public function close(): bool
    {
        if ($this->closed) {
            return true;
        }
        $this->closed = true;
        if ($this->heartbeatId > 0) {
            $this->timer->del($this->heartbeatId);
        }

        $handler = function () {
            while (!$this->channel->isEmpty()) {
                $connection = $this->channel->pop($this->poolOption->getWaitTimeout());
                if ($connection) {
                    $this->disConnection($connection);
                }
            }
            $this->channel->close();
        };

        $this->isCoroutine() ? $this->coroutine->create($handler) : $handler();
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
     * @throws Throwable
     */
    protected function initialize(): void
    {
        $this->channel = new Channel($this->context, $this->container->get(ChannelFactoryInterface::class), $this->poolOption->getMaxActive());
        $this->startHeartbeat();
        $handler = function () {
            $minActive = $this->poolOption->getMinActive();
            for ($i = 0; $i < $minActive; $i++) {
                $connection = $this->getConnection();
                if (!$this->channel->push($connection)) {
                    $this->disConnection($connection);
                }
            }
        };
        $handler();
    }

    /**
     * @return void
     */
    protected function initPoolOption(): void
    {
        $this->poolOption = new PoolOption(
            $this->options['min_active'],
            $this->options['max_active'],
            $this->options['max_lifetime'],
            $this->options['max_idle_time'],
            $this->options['wait_timeout'],
        );

        if (!$this->isCoroutine()) {
            $this->poolOption->setMinActive(1);
            $this->poolOption->setMaxActive(1);
        }
    }

    /**
     * @return PoolOptionInterface
     */
    public function getPoolOption(): PoolOptionInterface
    {
        return $this->poolOption;
    }

    /**
     * @return void
     */
    protected function startHeartbeat(): void
    {
        if ($this->options['heartbeat'] <= 0) {
            return;
        }
        $this->heartbeatId = $this->timer->tick(intval($this->options['heartbeat'] * 1000), function () {
            $this->checkLifetime();
            $this->checkMaxIdleTime();
        });
    }

    /**
     * @return void
     */
    protected function checkLifetime(): void
    {
        $now = time();
        $lifetime = $this->poolOption->getMaxLifetime();
        $length = $this->channel->length();
        for ($i = 0; $i < $length; $i++) {
            $connection = $this->channel->pop($this->poolOption->getWaitTimeout());
            if (!$connection->isConnected() || !$connection->ping() || ($lifetime > 0 && $now - $connection->getConnectTime() > $lifetime)) {
                $this->disConnection($connection);
            } else {
                $this->channel->push($connection);
            }
        }
    }

    /**
     * @return void
     */
    protected function checkMaxIdleTime(): void
    {
        $now = time();
        $connections = [];

        while (true) {
            if ($this->closed || $this->channel->isEmpty() || $this->connectionCount <= $this->poolOption->getMinActive()) {
                break;
            }
            $connection = $this->channel->pop($this->poolOption->getWaitTimeout());
            if (!$connection) {
                continue;
            }
            $lastActiveTime = $connection->getLastActiveTime();
            if ($now - $lastActiveTime < $this->poolOption->getMaxIdleTime()) {
                $connections[] = $connection;
            } else {
                $this->disConnection($connection);
            }
        }

        /** @var ConnectionInterface $connection */
        foreach ($connections as $connection) {
            if (!$this->channel->push($connection)) {
                $this->disConnection($connection);
            }
        }
    }

    /**
     * @return ConnectionInterface
     */
    protected function getConnection(): ConnectionInterface
    {
        $now = time();
        $connection = $this->createConnection();
        $connection->setPool($this);
        $connection->setLastActiveTime($now);
        $connection->setConnectTime($now);
        $connection->connect();
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
        $handler = function () use ($connection) {
            try {
                $connection->close();
            } catch (Throwable) {
            }
        };
        $this->isCoroutine() ? $this->coroutine->create($handler) : $handler();
    }

    /**
     * @return bool
     */
    protected function isCoroutine(): bool
    {
        return $this->context->inCoroutine() && !$this->context->inFiber();
    }

    public function __destruct()
    {
        if ($this->options['close_on_destruct']) {
            $this->close();
        }
    }
}