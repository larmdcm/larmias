<?php

declare(strict_types=1);

namespace Larmias\HttpServer\Emitter;

use Closure;
use Larmias\Contracts\Http\ResponseInterface;
use Larmias\HttpServer\Contracts\SseEmitterInterface;
use Larmias\HttpServer\Handler\SseHandler;

class SseEmitter implements SseEmitterInterface
{
    /**
     * @param Closure $callback
     */
    public function __construct(protected Closure $callback)
    {
    }

    /**
     * @param ResponseInterface $response
     * @return void
     */
    public function init(ResponseInterface $response): void
    {
        $response->header('Content-Type', 'text/event-stream')
            ->header('Cache-Control', 'no')
            ->header('Connection', 'keep-alive')
            ->header('X-Accel-Buffering', 'no');
    }

    /**
     * @param ResponseInterface $response
     * @return void
     */
    public function emit(ResponseInterface $response): void
    {
        $this->init($response);
        $handler = new SseHandler($response);
        call_user_func($this->callback, $handler);
    }
}