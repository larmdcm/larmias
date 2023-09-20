<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole\Concerns;

use Larmias\Engine\Swoole\Server;
use Swoole\Coroutine\Http\Server as HttpServer;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use Larmias\Engine\Swoole\Http\Request;
use Larmias\Engine\Swoole\Http\Response;

/**
 * @mixin Server
 */
trait WithHttpServer
{
    /**
     * @var HttpServer
     */
    protected HttpServer $httpServer;

    /**
     * @return void
     */
    public function initHttpServer(): void
    {
        $this->httpServer = new HttpServer($this->getWorkerConfig()->getHost(), $this->getWorkerConfig()->getPort(),
            $this->getSettings('ssl', false),
            $this->getSettings('reuse_port', true)
        );

        $this->httpServer->set($this->getServerSettings());
    }

    /**
     * @param SwooleRequest $req
     * @param SwooleResponse $resp
     * @return array
     */
    public function makeRequestAndResponse(SwooleRequest $req, SwooleResponse $resp): array
    {
        return [new Request($req), new Response($resp)];
    }

    /**
     * @return HttpServer
     */
    public function getHttpServer(): HttpServer
    {
        return $this->httpServer;
    }
}