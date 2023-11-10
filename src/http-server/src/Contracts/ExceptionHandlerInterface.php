<?php

declare(strict_types=1);

namespace Larmias\HttpServer\Contracts;

use Larmias\Contracts\ExceptionHandlerInterface as BaseExceptionHandlerInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

interface ExceptionHandlerInterface extends BaseExceptionHandlerInterface
{
    /**
     * @param ServerRequestInterface $request
     * @param Throwable $e
     * @return PsrResponseInterface
     */
    public function render(ServerRequestInterface $request, Throwable $e): PsrResponseInterface;
}