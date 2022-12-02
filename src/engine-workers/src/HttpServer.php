<?php

declare(strict_types=1);

namespace Larmias\Engine\WorkerS;

use Larmias\Engine\WorkerS\Http\Request;
use Larmias\Engine\WorkerS\Http\Response;
use Larmias\Engine\Event;
use Larmias\WorkerS\Constants\Event as WorkerEvent;
use Larmias\WorkerS\Manager;
use Larmias\WorkerS\Protocols\Http\Request as WorkerRequest;
use Larmias\WorkerS\Protocols\Http\Response as WorkerResponse;
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
    public function initialize(): void
    {
        parent::initialize();
        $this->server->on(WorkerEvent::ON_REQUEST, [$this, 'onRequest']);
    }

    /**
     * @param WorkerRequest $workerRequest
     * @param WorkerResponse $workerResponse
     * @return void
     */
    public function onRequest(WorkerRequest $workerRequest, WorkerResponse $workerResponse): void
    {
        try {
            $request = new Request($workerRequest);
            $response = new Response($workerResponse);
            $this->trigger(Event::ON_REQUEST, [$request, $response]);
        } catch (Throwable $e) {
            Manager::stopAll($e->getFile() . '('. $e->getLine() .')' . ':' . $e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
    }
}