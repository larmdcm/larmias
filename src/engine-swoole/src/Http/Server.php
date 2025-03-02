<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole\Http;

use Larmias\Engine\Swoole\Concerns\WithHttpServer;
use Larmias\Engine\Swoole\Server as BaseServer;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use Larmias\Engine\Event;
use Throwable;

class Server extends BaseServer
{
    use WithHttpServer;

    /**
     * @return void
     */
    public function process(): void
    {
        $this->initHttpServer();
        $this->initWaiter();

        $this->httpServer->handle('/', function (SwooleRequest $req, SwooleResponse $resp) {
            $this->waiter->add(function () use ($req, $resp) {
                try {
                    [$request, $response] = $this->makeRequestAndResponse($req, $resp);
                    $this->trigger(Event::ON_REQUEST, [$request, $response]);
                } catch (Throwable $e) {
                    $this->handleException($e);
                }
            });
        });

        $this->waiter->add(fn() => $this->httpServer->start());

        $this->wait(fn() => $this->shutdown());
    }

    /**
     * @return void
     */
    public function serverShutdown(): void
    {
        $this->httpServer->shutdown();
    }
}