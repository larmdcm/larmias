<?php

declare(strict_types=1);

namespace Larmias\HttpServer\Contracts;

use Larmias\Contracts\ExceptionReportHandlerInterface;
use Throwable;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

interface ExceptionHandlerInterface extends ExceptionReportHandlerInterface
{
    public function render(RequestInterface $request,Throwable $e): PsrResponseInterface;
}