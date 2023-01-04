<?php

declare(strict_types=1);

namespace Larmias\HttpServer\Exceptions\Handler;

use Larmias\ExceptionHandler\ExceptionHandler as BaseExceptionHandler;

use Larmias\HttpServer\Contracts\ExceptionHandlerInterface;
use Larmias\HttpServer\Contracts\RequestInterface;
use Larmias\HttpServer\Contracts\ResponseInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Throwable;

class ExceptionHandler extends BaseExceptionHandler implements ExceptionHandlerInterface
{
    public function render(RequestInterface $request,Throwable $e): PsrResponseInterface
    {
        /** @var ResponseInterface $response */
        $response = $this->container->make(ResponseInterface::class);
        $data = $this->collectExceptionToArray($e);
        return $response->json($data);
    }
}