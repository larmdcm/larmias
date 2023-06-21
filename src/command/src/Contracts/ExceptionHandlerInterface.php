<?php

declare(strict_types=1);

namespace Larmias\Command\Contracts;

use Larmias\Command\Command;
use Larmias\Contracts\ExceptionHandlerInterface as BaseExceptionHandlerInterface;
use Throwable;

interface ExceptionHandlerInterface extends BaseExceptionHandlerInterface
{
    /**
     * @param Command $command
     * @param Throwable $e
     * @return int
     */
    public function render(Command $command, Throwable $e): int;
}