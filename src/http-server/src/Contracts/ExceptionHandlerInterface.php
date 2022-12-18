<?php

declare(strict_types=1);

namespace Larmias\HttpServer\Contracts;

use Larmias\Contracts\ExceptionReportHandlerInterface;
use Throwable;

interface ExceptionHandlerInterface extends ExceptionReportHandlerInterface
{
    /**
     * @param RequestInterface $request
     * @param \Throwable $e
     * @return ResponseInterface
     */
    public function render(RequestInterface $request,Throwable $e): ResponseInterface;
}