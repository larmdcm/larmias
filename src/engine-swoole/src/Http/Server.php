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

        $this->server->handle('/', function (SwooleRequest $request, SwooleResponse $response) {
            try {
                $request = new Request($request);
                $response = new Response($response);
                $this->trigger(Event::ON_REQUEST, [$request, $response]);
            } catch (Throwable $e) {
                $this->exceptionHandler($e);
            }
        });

        $this->server->start();
    }
}