<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole\Http;

use Larmias\Engine\Swoole\Concerns\WithHttpServer;
use Larmias\Engine\Swoole\Server as BaseServer;
use Swoole\Coroutine\Socket;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use Larmias\Engine\Event;
use Throwable;

class Server extends BaseServer
{
    use WithHttpServer;

    /**
     * @var array<int,Connection>
     */
    protected array $connections = [];

    /**
     * @return void
     */
    public function process(): void
    {
        $this->initServer();
        $this->initHttpServer();
        $this->httpServer->handle('/', function (SwooleRequest $req, SwooleResponse $resp) {
            $this->waiter->add(function () use ($req, $resp) {
                /** @var Socket $socket */
                $socket = $resp->socket;
                try {
                    $this->socketHeartbeatCheck($socket);
                    [$request, $response] = $this->makeRequestAndResponse($req, $resp);
                    $this->trigger(Event::ON_REQUEST, [$request, $response]);
                    if (isset($this->connections[$socket->fd])) {
                        $this->connections[$socket->fd]->processing = false;
                    }
                } catch (Throwable $e) {
                    $this->handleException($e);
                }
            });
        });

        $this->waiter->add(fn() => $this->start());

        $this->wait(fn() => $this->shutdown());
    }

    /**
     * @return void
     */
    public function onServerStart(): void
    {
        $this->httpServer->start();
    }

    /**
     * @return void
     */
    public function onServerShutdown(): void
    {
        $this->httpServer->shutdown();
        $this->connections = [];
    }

    /**
     * @param Socket $socket
     * @return void
     */
    protected function socketHeartbeatCheck(Socket $socket): void
    {
        if (!$this->isOpenHeartbeatCheck()) {
            return;
        }
        if (!isset($this->connections[$socket->fd])) {
            $connection = new Connection($socket);
            $connection->onClose = function (Connection $conn) {
                unset($this->connections[$conn->getFd()]);
            };
            $this->connections[$socket->fd] = $connection;
        }
        $this->connHeartbeatCheck($this->connections[$socket->fd]);
    }
}