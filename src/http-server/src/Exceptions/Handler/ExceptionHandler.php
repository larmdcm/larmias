<?php

declare(strict_types=1);

namespace Larmias\HttpServer\Exceptions\Handler;

use Larmias\ExceptionHandler\ExceptionHandler as BaseExceptionHandler;

use Larmias\HttpServer\Contracts\ExceptionHandlerInterface;
use Larmias\HttpServer\Contracts\RequestInterface;
use Larmias\HttpServer\Contracts\ResponseInterface;
use Larmias\Routing\Exceptions\RouteMethodNotAllowedException;
use Larmias\Routing\Exceptions\RouteNotFoundException;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Throwable;

class ExceptionHandler extends BaseExceptionHandler implements ExceptionHandlerInterface
{
    public function render(RequestInterface $request, Throwable $e): PsrResponseInterface
    {
        /** @var ResponseInterface $response */
        $response = $this->container->make(ResponseInterface::class);
        $data = $this->collectExceptionToArray($e);
        $code = 500;
        if ($e instanceof RouteNotFoundException) {
            $code = 404;
        } else if ($e instanceof RouteMethodNotAllowedException) {
            $code = 403;
        }
        return $response->json($data, $code);
    }
}