<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole\Http;

use Larmias\Contracts\Tcp\ConnectionInterface;
use Larmias\Engine\Swoole\Concerns\WithTcpConnection;
use Swoole\Coroutine\Socket;
use Closure;

class Connection implements ConnectionInterface
{
    use WithTcpConnection;

    public ?Closure $onClose = null;

    public function __construct(protected Socket $socket)
    {
    }

    public function recv(): mixed
    {
        return null;
    }

    public function getId(): int
    {
        return $this->getFd();
    }

    public function send(mixed $data): bool
    {
        return false;
    }

    public function close(): bool
    {
        if ($this->socket->isClosed()) {
            return true;
        }
        if ($this->onClose) {
            call_user_func($this->onClose, $this);
        }
        return $this->socket->close();
    }

    public function getRawConnection(): Socket
    {
        return $this->socket;
    }

    public function getFd(): int
    {
        return $this->socket->fd;
    }
}