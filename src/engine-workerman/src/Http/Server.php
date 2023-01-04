<?php

declare(strict_types=1);

namespace Larmias\Engine\WorkerMan\Http;

use Larmias\Engine\Event;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Request as WorkerRequest;
use Throwable;
use Larmias\Engine\WorkerMan\Server as BaseServer;

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
            $this->exceptionHandler($e);
        }
    }
}