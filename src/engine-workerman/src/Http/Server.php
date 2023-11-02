<?php

declare(strict_types=1);

namespace Larmias\Engine\WorkerMan\Http;

use Larmias\Context\Context;
use Larmias\Engine\Event;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Request as WorkerRequest;
use Throwable;
use Larmias\Engine\WorkerMan\Server as BaseServer;
use Larmias\Engine\WorkerMan\Context as WorkerContext;

class Server extends BaseServer
{
    /**
     * @var string
     */
    protected string $protocol = 'http';

    /**
     * @return void
     */
    public function onMessage(TcpConnection $connection, WorkerRequest $request): void
    {
        try {
            $request = new Request($request);
            $response = new Response($connection);
            $this->trigger(Event::ON_REQUEST, [$request, $response]);
        } catch (Throwable $e) {
            $this->handleException($e);
        } finally {
            /** @var WorkerContext $context */
            $context = Context::getContext();
            $context->clear();
        }
    }
}