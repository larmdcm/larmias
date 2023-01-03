<?php

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Larmias\HttpServer\Contracts\ResponseInterface;
use Larmias\HttpServer\Contracts\RequestInterface;
use Larmias\Di\Container;

class Prev implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): PsrResponseInterface
    {
        /** @var ResponseInterface $response */
        $response = Container::getInstance()->make(ResponseInterface::class);
        if ($request->getUri() == '/favicon.ico') {
            return $response->raw('favicon');
        }
        var_dump($request instanceof RequestInterface);
        println('Prev::process');
        return $handler->handle($request);
    }
}

class CheckAuth implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): PsrResponseInterface
    {
        $response = $handler->handle($request);
        return $response->withStatus(404);
    }
}

return [
    'http' => [
        Prev::class,
        function (ServerRequestInterface $request,RequestHandlerInterface $handler): PsrResponseInterface {
            dump('After');
            return $handler->handle($request);
        }
    ]
];