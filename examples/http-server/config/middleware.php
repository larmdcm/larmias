<?php

use Larmias\HttpServer\Contracts\RequestInterface;
use Larmias\HttpServer\Contracts\MiddlewareInterface;
use Larmias\HttpServer\Contracts\ResponseInterface;
use Larmias\Di\Container;

class Prev implements MiddlewareInterface
{
    public function process(RequestInterface $request,Closure $next): ResponseInterface
    {
        if ($request->getUri() == '/favicon.ico') {
            return Container::getInstance()->make(ResponseInterface::class)->raw('favicon');
        }
        println('Prev::process');
        return $next($request);
    }
}

class CheckAuth implements MiddlewareInterface
{
    public function process(RequestInterface $request,Closure $next): ResponseInterface
    {
        println('CheckAuth::process');
        return $next($request);
    }
}

return [
    'http' => [
        Prev::class,
    ]
];