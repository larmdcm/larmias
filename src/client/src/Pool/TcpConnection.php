<?php

declare(strict_types=1);

namespace Larmias\Client\Pool;

use Larmias\Client\Socket;
use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\Tcp\ConnectionInterface;
use Larmias\Pool\Connection as BaseConnection;
use Larmias\Client\Connections\TcpConnection as TcpConn;
use Throwable;

class TcpConnection extends BaseConnection implements ConnectionInterface
{
    /**
     * @var array
     */
    protected array $config = [
        'host' => '127.0.0.1',
        'port' => 2000,
        'timeout' => 3,
        'connect_try_timeout' => 0,
        'packer_class' => null,
        'read_buffer_size' => 87380,
        'max_package_size' => 1048576,
    ];

    protected TcpConn $connection;

    /**
     * @param ContainerInterface $container
     * @param array $config
     * @throws Throwable
     */
    public function __construct(
        protected ContainerInterface $container,
        array                        $config = []
    )
    {
        $this->connection = new TcpConn($this->container, $config);
    }

    /**
     * @return bool
     */
    public function connect(): bool
    {
        return $this->connection->connect();
    }

    public function send(mixed $data): bool
    {
        return $this->connection->send($data);
    }

    /**
     * @return mixed
     */
    public function recv(): mixed
    {
        return $this->connection->recv();
    }

    /**
     * @return bool
     */
    public function isConnected(): bool
    {
        return $this->connection->isConnected();
    }

    /**
     * @return bool
     */
    public function close(): bool
    {
        return $this->connection->close();
    }

    /**
     * @return bool
     */
    public function ping(): bool
    {
        return true;
    }

    public function getRawConnection(): Socket
    {
        return $this->connection->getRawConnection();
    }

    public function getId(): int
    {
        return $this->connection->getId();
    }

    public function getFd(): int
    {
        return $this->connection->getFd();
    }
}