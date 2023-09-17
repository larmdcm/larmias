<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole\Tcp;

use Larmias\Contracts\Tcp\ConnectionInterface;
use Larmias\Engine\Swoole\Contracts\PackerInterface;
use Swoole\Coroutine\Server\Connection as TcpConnection;

class Connection implements ConnectionInterface
{
    /**
     * @param int $id
     * @param TcpConnection $connection
     * @param PackerInterface $packer
     */
    public function __construct(protected int $id, protected TcpConnection $connection, protected PackerInterface $packer)
    {
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getFd(): int
    {
        return $this->connection->exportSocket()->fd;
    }

    /**
     * @param mixed $data
     * @return mixed
     */
    public function send(mixed $data): mixed
    {
        return $this->connection->send($this->packer->pack((string)$data));
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
    public function close(): bool
    {
        return $this->connection->close();
    }

    /**
     * @return TcpConnection
     */
    public function getRawConnection(): TcpConnection
    {
        return $this->connection;
    }
}