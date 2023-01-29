<?php

declare(strict_types=1);

namespace Larmias\Http\CSRF\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CsrfMiddleware implements MiddlewareInterface
{
    /**
     * @var array
     */
    protected array $except = [];

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $handler->handle($request);
    }

    /**
     * Determine if the HTTP request uses a ‘read’ verb.
     *
     * @param ServerRequestInterface $request
     * @return bool
     */
    protected function isReading(ServerRequestInterface $request): bool
    {
        return \in_array($request->getMethod(), ['HEAD', 'GET', 'OPTIONS'], true);
    }
}