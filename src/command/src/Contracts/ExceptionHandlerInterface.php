<?php

declare(strict_types=1);

namespace Larmias\Command\Contracts;

use Larmias\Command\Command;
use Larmias\Contracts\ExceptionReportHandlerInterface;

interface ExceptionHandlerInterface extends ExceptionReportHandlerInterface
{
    /**
     * @param Command $command
     * @param \Throwable $e
     * @return void
     */
    public function render(Command $command, \Throwable $e): void;
}