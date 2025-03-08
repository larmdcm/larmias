<?php

declare(strict_types=1);

namespace Larmias\Engine\WorkerMan\WebSocket;

use Larmias\Engine\Event;
use Larmias\Engine\WorkerMan\Server as BaseServer;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Request;
use Throwable;

class Server extends BaseServer
{
    /**
     * @var string
     */
    protected string $protocol = 'websocket';

    /**
     * @param TcpConnection $tcpConnection
     * @param Request $request
     * @return void
     */
    public function onWebSocketConnect(TcpConnection $tcpConnection, Request $request): void
    {
        try {
            $tcpConnection->request = $request;
            $connection = new Connection($tcpConnection);
            $this->trigger(Event::ON_OPEN, [$connection]);
        } catch (Throwable $e) {
            $this->handleException($e);
        }
    }

    /**
     * @param TcpConnection $tcpConnection
     * @param mixed $data
     * @return void
     */
    public function onMessage(TcpConnection $tcpConnection, mixed $data): void
    {
        try {
            $connection = new Connection($tcpConnection);
            $frame = Frame::from($connection->getFd(), $data);
            $this->trigger(Event::ON_MESSAGE, [$connection, $frame]);
        } catch (Throwable $e) {
            $this->handleException($e);
        }
    }

    /**
     * @param TcpConnection $tcpConnection
     * @return void
     */
    public function onWebSocketClose(TcpConnection $tcpConnection): void
    {
        try {
            $connection = new Connection($tcpConnection);
            $this->trigger(Event::ON_CLOSE, [$connection]);
        } catch (Throwable $e) {
            $this->handleException($e);
        }
    }
}