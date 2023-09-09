<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole\Udp;

use Larmias\Contracts\Udp\ConnectionInterface;
use Swoole\Coroutine\Socket;

class Connection implements ConnectionInterface
{
    public function __construct(protected Socket $socket, protected mixed $peer)
    {
    }

    public function send(mixed $data): mixed
    {
        return $this->socket->sendto($this->peer['address'], $this->peer['port'], $data);
    }

    public function close(mixed $data = null): bool
    {
        if ($data !== null) {
            $this->send($data);
        }

        return true;
    }

    public function getRawConnection(): object
    {
        return $this->socket;
    }
}