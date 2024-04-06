<?php

declare(strict_types=1);

namespace Larmias\Auth\Middleware;

use Larmias\Auth\Traits\Authenticate;
use Larmias\Contracts\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Closure;

class AuthenticateMiddleware implements MiddlewareInterface
{
    use Authenticate;

    /**
     * @var array|null[]
     */
    protected array $guards = [null];

    /**
     * @param ContainerInterface $container
     */
    public function __construct(protected ContainerInterface $container)
    {
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->setAuthManager();
        $this->authenticate($request, $this->guards);
        return $this->checkAuth(function () use ($handler, $request) {
            return $handler->handle($request);
        });
    }

    /**
     * @param Closure $next
     * @return ResponseInterface
     */
    protected function checkAuth(Closure $next): ResponseInterface
    {
        return $next();
    }
}