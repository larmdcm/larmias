<?php

declare(strict_types=1);

namespace Larmias\Command\Contracts;

use Larmias\Command\Command;
use Larmias\Contracts\ExceptionHandlerInterface;

interface ExceptionHandlerInterface extends ExceptionHandlerInterface
{
    /**
     * @param Command $command
     * @param \Throwable $e
     * @return void
     */
    public function render(Command $command, \Throwable $e): void;
}