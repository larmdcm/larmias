<?php

declare(strict_types=1);

namespace Larmias\Command\Exceptions\Handler;

use Larmias\Command\Command;
use Larmias\Command\Contracts\ExceptionHandlerInterface;
use Larmias\ExceptionHandler\ExceptionHandler as BaseExceptionHandler;
use Throwable;

class ExceptionHandler extends BaseExceptionHandler implements ExceptionHandlerInterface
{
    /**
     * @param Command $command
     * @param Throwable $e
     * @return void
     * @throws Throwable
     */
    public function render(Command $command, Throwable $e): void
    {
        throw $e;
    }
}