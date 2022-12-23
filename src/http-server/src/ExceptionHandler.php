<?php

declare(strict_types=1);

namespace Larmias\HttpServer;

use Larmias\ExceptionHandler\ExceptionHandler as BaseExceptionHandler;

use Larmias\HttpServer\Contracts\ExceptionHandlerInterface;
use Larmias\HttpServer\Contracts\RequestInterface;
use Larmias\HttpServer\Contracts\ResponseInterface;
use Throwable;

class ExceptionHandler extends BaseExceptionHandler implements ExceptionHandlerInterface
{
    /**
     * 异常渲染处理
     * @param \Larmias\HttpServer\Contracts\RequestInterface $request
     * @param \Throwable $e
     * @return \Larmias\HttpServer\Contracts\ResponseInterface
     */
    public function render(RequestInterface $request,Throwable $e): ResponseInterface
    {
        /** @var ResponseInterface $response */
        $response = $this->container->make(ResponseInterface::class);
        $data = $this->collectExceptionToArray($e);
        return $response->json($data);
    }
}