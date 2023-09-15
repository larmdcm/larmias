<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole\Http;

use Larmias\Engine\Swoole\Server as BaseServer;
use Swoole\Coroutine\Http\Server as HttpServer;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use Larmias\Engine\Event;
use Throwable;

class Server extends BaseServer
{
    /**
     * @var HttpServer
     */
    protected HttpServer $server;

    /**
     * @return void
     */
    public function process(): void
    {
        $this->server = new HttpServer($this->getWorkerConfig()->getHost(), $this->getWorkerConfig()->getPort(),
            $this->getSettings('ssl', false),
            $this->getSettings('reuse_port', true)
        );

        $this->server->set($this->getServerSettings());

        $this->server->handle('/', function (SwooleRequest $req, SwooleResponse $resp) {
            try {
                $request = new Request($req);
                $response = new Response($resp);
                $this->trigger(Event::ON_REQUEST, [$request, $response]);
            } catch (Throwable $e) {
                $this->handleException($e);
            }
        });

        $this->server->start();
    }
}