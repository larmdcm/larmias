<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole\Tcp;

use Larmias\Contracts\Tcp\ConnectionInterface;
use Larmias\Engine\Swoole\Contracts\PackerInterface;
use Swoole\Coroutine\Server\Connection as TCPConnection;

class Connection implements ConnectionInterface
{
    /**
     * @param TCPConnection $connection
     * @param PackerInterface $packer
     */
    public function __construct(protected TCPConnection $connection, protected PackerInterface $packer)
    {
    }

    /**
     * @return int
     */
    public function getId(): int
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
     * @param mixed $data
     * @return bool
     */
    public function close(mixed $data = null): bool
    {
        if ($data !== null) {
            $this->send($data);
        }
        return $this->connection->close();
    }

    /**
     * @return TCPConnection
     */
    public function getRawConnection(): TCPConnection
    {
        return $this->connection;
    }
}