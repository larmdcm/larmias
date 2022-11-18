<?php

declare(strict_types=1);

namespace Larmias\Contracts;

use Throwable;

interface ExceptionReportHandlerInterface
{
    /**
     * @param \Throwable $e
     */
    public function report(Throwable $e): void;
}