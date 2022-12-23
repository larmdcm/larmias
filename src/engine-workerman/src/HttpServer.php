<?php

declare(strict_types=1);

namespace Larmias\Engine\WorkerMan;

use Larmias\Engine\Event;
use Larmias\Engine\WorkerMan\Http\Request;
use Larmias\Engine\WorkerMan\Http\Response;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Request as WorkerRequest;
use Throwable;

class HttpServer extends Server
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