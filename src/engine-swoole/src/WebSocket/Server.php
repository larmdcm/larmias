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
use Swoole\Coroutine;
use Throwable;

class Server extends BaseServer
{
    use WithHttpServer, WithIdAtomic;

    /**
     * @return void
     */
    public function process(): void
    {
        $this->initIdAtomic();
        $this->initHttpServer();

        $this->httpServer->handle('/', function (SwooleRequest $req, SwooleResponse $resp) {
            try {
                $resp->upgrade();

                [$request, $response] = $this->makeRequestAndResponse($req, $resp);

                $connection = new Connection($this->generateId(), $request, $response);

                Coroutine::create(fn() => $this->trigger(Event::ON_OPEN, [$connection]));

                while (true) {
                    $data = $connection->recv();
                    if ($data === '' || $data === false || $data instanceof CloseFrame) {
                        break;
                    }

                    Coroutine::create(function () use ($connection, $data) {
                        $frame = Frame::from($data);
                        $this->trigger(Event::ON_MESSAGE, [$connection, $frame]);
                    });
                }

                $connection->close();
                Coroutine::create(fn() => $this->trigger(Event::ON_CLOSE, [$connection]));
            } catch (Throwable $e) {
                $this->handleException($e);
            }
        });

        $this->httpServer->start();
    }
}