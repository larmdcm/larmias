<?php

declare(strict_types=1);

namespace Larmias\HttpServer\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Closure;

class RequestHandler implements RequestHandlerInterface
{
    public function __construct(protected Closure $handler)
    {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return \call_user_func($this->handler, $request);
    }
}