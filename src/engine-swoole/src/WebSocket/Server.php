<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole\WebSocket;

use Larmias\Engine\Swoole\Concerns\WithHttpServer;
use Larmias\Engine\Swoole\Concerns\WithIdAtomic;
use Larmias\Engine\Swoole\Server as BaseServer;
use Larmias\Engine\Event;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use Swoole\WebSocket\CloseFrame;
use Throwable;

class Server extends BaseServer
{
    use WithHttpServer, WithIdAtomic;

    /**
     * @return void
     */
    public function process(): void
    {
        $this->initServer();
        $this->initHttpServer();

        $this->httpServer->handle('/', function (SwooleRequest $req, SwooleResponse $resp) {
            try {
                $resp->upgrade();

                [$request, $response] = $this->makeRequestAndResponse($req, $resp);

                $connection = new Connection($this->generateId(), $request, $response);
                $this->join($connection);
                $this->waiter->add(function () use ($connection) {
                    $this->trigger(Event::ON_OPEN, [$connection]);
                });

                while ($this->running) {
                    $data = $connection->recv();
                    if ($data === '' || $data === false || $data instanceof CloseFrame) {
                        break;
                    }

                    $this->waiter->add(function () use ($connection, $data) {
                        $frame = Frame::from($data);
                        $this->connHeartbeatCheck($connection);
                        $this->trigger(Event::ON_MESSAGE, [$connection, $frame]);
                        $connection->processing = false;
                    });
                }

                $connection->close();
                $this->waiter->add(function () use ($connection) {
                    $this->trigger(Event::ON_CLOSE, [$connection]);
                });
            } catch (Throwable $e) {
                $this->handleException($e);
            }
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
    }
}