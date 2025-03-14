<?php

declare(strict_types=1);

namespace Larmias\Client\Proxy;

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\ContextInterface;
use Larmias\Contracts\Coroutine\CoroutineInterface;
use Larmias\Client\Pool\TcpClientConnection;
use Larmias\Client\Pool\TcpClientPool;
use Throwable;

/**
 * @method bool connect()
 * @method int|false send(string $data)
 * @method mixed recv(int $length = 65535)
 * @method mixed sendAndRecv(string $data, array $options = [])
 * @method bool close()
 * @method bool destroy(bool $destroy = true)
 * @method bool reconnect()
 * @method bool ping()
 * @method bool isConnected()
 */
class TcpClient
{
    /**
     * @var TcpClientPool
     */
    protected TcpClientPool $clientPool;

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
     * @throws Throwable
     */
    public function __construct(protected ContainerInterface $container, protected array $config = [])
    {
        $this->context = $this->container->get(ContextInterface::class);
        if ($this->context->inCoroutine() && !$this->context->inFiber()) {
            $this->coroutine = $this->container->get(CoroutineInterface::class);
        }
        $this->clientPool = new TcpClientPool($this->container, $this->config['pool'] ?? [], $this->config);
    }

    /**
     * @param bool $hasContextConnection
     * @return TcpClientConnection
     */
    protected function getConnection(bool $hasContextConnection = false): TcpClientConnection
    {
        if ($hasContextConnection) {
            return $this->context->get($this->getContextKey());
        }
        /** @var TcpClientConnection $connection */
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
                $connection->release();
            }
        }
    }

    /**
     * @return string
     */
    protected function getContextKey(): string
    {
        return 'client.tcp.connection';
    }
}