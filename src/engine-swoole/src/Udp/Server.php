<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole\Udp;

use Larmias\Engine\Event;
use Larmias\Engine\Swoole\Server as BaseServer;
use Swoole\Coroutine\Socket;
use RuntimeException;
use Throwable;
use const AF_INET;
use const SOCK_DGRAM;

class Server extends BaseServer
{
    protected Socket $socket;

    /**
     * @return void
     */
    public function process(): void
    {
        $this->initWaiter();
        $this->socket = new Socket(AF_INET, SOCK_DGRAM, 0);
        [$host, $port] = [$this->getWorkerConfig()->getHost(), $this->getWorkerConfig()->getPort()];
        if (!$this->socket->bind($host, $port)) {
            throw new RuntimeException("bind({$host}:{$port}) failed", $this->socket->errCode);
        }

        $this->waiter->add(function () {
            while ($this->running) {
                $peer = null;
                $data = $this->socket->recvfrom($peer);
                $connection = new Connection($this->socket, $peer);
                $this->waiter->add(function () use ($connection, $data) {
                    try {
                        $this->trigger(Event::ON_PACKET, [$connection, $data]);
                    } catch (Throwable $e) {
                        $this->handleException($e);
                    }
                });
            }
        });

        $this->wait(fn() => $this->shutdown());
    }

    /**
     * @return void
     */
    public function serverShutdown(): void
    {
        $this->socket->cancel();
    }
}