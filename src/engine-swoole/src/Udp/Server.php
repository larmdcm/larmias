<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole\Udp;

use Larmias\Engine\Event;
use Larmias\Engine\Swoole\Server as BaseServer;
use Swoole\Coroutine\Socket;
use Swoole\Coroutine;
use RuntimeException;
use Throwable;
use const AF_INET;
use const SOCK_DGRAM;

class Server extends BaseServer
{
    /**
     * @return void
     */
    public function process(): void
    {
        $socket = new Socket(AF_INET, SOCK_DGRAM, 0);
        [$host, $port] = [$this->getWorkerConfig()->getHost(), $this->getWorkerConfig()->getPort()];
        if (!$socket->bind($host, $port)) {
            throw new RuntimeException("bind({$host}:{$port}) failed", $socket->errCode);
        }

        while (true) {
            $peer = null;
            $data = $socket->recvfrom($peer);
            $connection = new Connection($socket, $peer);
            Coroutine::create(function () use ($connection, $data) {
                try {
                    $this->trigger(Event::ON_PACKET, [$connection, $data]);
                } catch (Throwable $e) {
                    $this->handleException($e);
                }
            });
        }
    }
}