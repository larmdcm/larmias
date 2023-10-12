<?php

declare(strict_types=1);

namespace Larmias\Trace\Middleware;

use Larmias\Trace\Contracts\TraceContextInterface;
use Larmias\Trace\Contracts\TraceInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class HttpTraceMiddleware implements MiddlewareInterface
{
    public function __construct(protected TraceContextInterface $traceContext)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $basicCollector = $this->traceContext->getContextForTrace()->getCollector(TraceInterface::BASIC);
        $basicCollector->beforeHandle(['request' => $request]);
        $response = $handler->handle($request);
        $basicCollector->afterHandle(['request' => $request, 'response' => $response]);
        return $response;
    }
}