<?php

declare(strict_types=1);

namespace Larmias\HttpServer\Contracts;

interface MiddlewareInterface
{
    /**
     * @param \Larmias\HttpServer\Contracts\RequestInterface $request
     * @param \Closure $next
     * @return \Larmias\HttpServer\Contracts\ResponseInterface
     */
    public function process(RequestInterface $request, \Closure $next): ResponseInterface;
}