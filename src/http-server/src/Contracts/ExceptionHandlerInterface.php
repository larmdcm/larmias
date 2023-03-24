<?php

declare(strict_types=1);

namespace Larmias\HttpServer\Contracts;

use Larmias\Contracts\ExceptionReportHandlerInterface;
use Throwable;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

interface ExceptionHandlerInterface extends ExceptionReportHandlerInterface
{
	/**
	 * @param  RequestInterface $request [description]
	 * @param  Throwable        $e       [description]
	 * @return [type]                    [description]
	 */
    public function render(RequestInterface $request,Throwable $e): PsrResponseInterface;
}