<?php

declare(strict_types=1);

namespace Larmias\Engine\Drivers\WorkerS;

use Larmias\Engine\Event;
use Larmias\WorkerS\Constants\Event as WorkerEvent;
use Larmias\WorkerS\Manager;
use Larmias\WorkerS\Protocols\Http\Request;
use Larmias\WorkerS\Protocols\Http\Response;
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
     * @param Request $request
     * @param Response $response
     * @return void
     */
    public function onRequest(Request $request, Response $response): void
    {
        try {
            $this->trigger(Event::ON_REQUEST, [$request, $response]);
        } catch (Throwable $e) {
            Manager::stopAll($e->getMessage());
        }
    }
}