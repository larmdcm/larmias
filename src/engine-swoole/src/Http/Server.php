<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole\Http;

use Larmias\Engine\Swoole\Coroutine;
use Larmias\Engine\Swoole\Server as BaseServer;
use Swoole\Coroutine\Http\Server as HttpServer;
use Swoole\Http\Request;
use Swoole\Http\Response;

class Server extends BaseServer
{
    /**
     * @var HttpServer
     */
    protected HttpServer $server;

    /**
     * @return void
     */
    public function initServer(): void
    {
        $this->server = new HttpServer($this->getWorkerConfig()->getHost(), $this->getWorkerConfig()->getPort(),
            $this->getSettings('ssl', false),
            $this->getSettings('reuse_port', true)
        );

        $this->server->handle('/', function (Request $request, Response $response) {
            Coroutine::create(function () use ($request, $response) {
                $response->end('hello,world!');
            });
        });

        $this->server->start();
    }
}