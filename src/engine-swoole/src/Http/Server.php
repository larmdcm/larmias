<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole\Http;

use Larmias\Engine\Swoole\Concerns\WithHttpServer;
use Larmias\Engine\Swoole\Server as BaseServer;
use Swoole\Coroutine;
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

        $this->httpServer->handle('/', function (SwooleRequest $req, SwooleResponse $resp) {
            Coroutine::create(function () use ($req, $resp) {
                try {
                    [$request, $response] = $this->makeRequestAndResponse($req, $resp);
                    $this->trigger(Event::ON_REQUEST, [$request, $response]);
                } catch (Throwable $e) {
                    $this->handleException($e);
                }
            });
        });

        $this->httpServer->start();
    }
}