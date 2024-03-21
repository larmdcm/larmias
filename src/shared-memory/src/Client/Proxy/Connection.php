<?php

declare(strict_types=1);

namespace Larmias\SharedMemory\Client\Proxy;

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\ContextInterface;
use Larmias\Contracts\Coroutine\CoroutineInterface;
use Larmias\SharedMemory\Client\Pool\ClientConnection;
use Larmias\SharedMemory\Client\Pool\ClientPool;

class Connection
{
    /**
     * @var ClientPool
     */
    protected ClientPool $clientPool;

    /**
     * @var ContextInterface
     */
    protected ContextInterface $context;

    /**
     * @var CoroutineInterface|null
     */
    protected ?CoroutineInterface $coroutine = null;

    /**
     * @param ContainerInterface $container
     * @param array $config
     * @throws \Throwable
     */
    public function __construct(protected ContainerInterface $container, protected array $config = [])
    {
        $this->context = $this->container->get(ContextInterface::class);
        if ($this->context->inCoroutine()) {
            $this->coroutine = $this->container->get(CoroutineInterface::class);
        }
        $this->clientPool = new ClientPool($this->container, $this->config['pool'] ?? [], $this->config);
    }

    /**
     * @param bool $hasContextConnection
     * @return ClientConnection
     */
    public function getConnection(bool $hasContextConnection = false): ClientConnection
    {
        if ($hasContextConnection) {
            return $this->context->get($this->getContextKey());
        }
        /** @var ClientConnection $connection */
        $connection = $this->clientPool->get();
        return $connection;
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        $contextKey = $this->getContextKey();
        $hasContextConnection = $this->context->has($contextKey);
        $connection = $this->getConnection($hasContextConnection);
        try {
            return $connection->{$name}(...$arguments);
        } finally {
            if (!$hasContextConnection) {
                if ($this->shouldUseSameConnection($name)) {
                    if ($name === 'select' && isset($arguments[0])) {
                        $connection->setDatabase((string)$arguments[0]);
                    }
                    $this->context->set($contextKey, $connection);
                    $handler = function () use ($contextKey, $connection) {
                        $this->context->destroy($contextKey);
                        $connection->release();
                    };
                    $this->coroutine ? $this->coroutine->defer($handler) : $handler();
                } else {
                    $connection->release();
                }
            }
        }
    }

    /**
     * @param string $name
     * @return bool
     */
    protected function shouldUseSameConnection(string $name): bool
    {
        return in_array($name, ['select']);
    }

    /**
     * @return string
     */
    protected function getContextKey(): string
    {
        return 'shared-memory.client.connections.' . $this->config['name'];
    }

    /**
     * @return bool
     */
    public function connect(): bool
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * @return bool
     */
    public function reconnect(): bool
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * @return bool
     */
    public function close(): bool
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * @return \Redis
     */
    public function getRaw(): \Redis
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * @return int
     */
    public function getDatabase(): int
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * @param int $db
     * @return void
     */
    public function setDatabase(int $db): void
    {
        $this->__call(__FUNCTION__, func_get_args());
    }
}