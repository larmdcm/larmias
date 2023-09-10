<?php

declare(strict_types=1);

namespace Larmias\ExceptionHandler\Contracts;

use Throwable;

interface ExceptionHandlerDispatcherInterface
{
    /**
     * 调度异常处理
     * @param Throwable $e
     * @param array $handlers
     * @param mixed $args
     * @return mixed
     */
    public function dispatch(Throwable $e, array $handlers, mixed $args = null): mixed;
}