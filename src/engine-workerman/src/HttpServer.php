<?php

declare(strict_types=1);

namespace Larmias\Engine\WorkerMan;

use Larmias\Engine\Event;
use Larmias\Engine\WorkerMan\Http\Request;
use Larmias\Engine\WorkerMan\Http\Response;
use Throwable;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Request as WorkerRequest;
use Workerman\Worker;

class HttpServer extends Server
{
    /**
     * @var string
     */
    protected string $protocol = 'http';

    /**
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->server->onMessage = [$this,'onRequest'];
    }

    /**
     * @return void
     */
    public function onRequest(TcpConnection $connection, WorkerRequest $request): void
    {
        try {
            $request = new Request($request);
            $response = new Response($connection);
            $this->trigger(Event::ON_REQUEST, [$request, $response]);
        } catch (Throwable $e) {
            Worker::stopAll(log: $e->getFile() . '('. $e->getLine() .')' . ':' . $e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
    }
}