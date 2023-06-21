<?php

declare(strict_types=1);

namespace Larmias\Engine\WorkerMan\Udp;

use Larmias\Engine\Event;
use Larmias\Engine\WorkerMan\Server as BaseServer;
use Workerman\Connection\UdpConnection;
use Throwable;

class Server extends BaseServer
{
    /**
     * @var string
     */
    protected string $protocol = 'udp';

    /**
     * @param UdpConnection $udpConnection
     * @param mixed $data
     * @return void
     */
    public function onMessage(UdpConnection $udpConnection, mixed $data): void
    {
        try {
            $connection = new Connection($udpConnection);
            $this->trigger(Event::ON_PACKET, [$connection, $data]);
        } catch (Throwable $e) {
            $this->handleException($e);
        }
    }
}