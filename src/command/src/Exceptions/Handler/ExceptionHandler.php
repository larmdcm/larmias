<?php

declare(strict_types=1);

namespace Larmias\Command\Exceptions\Handler;

use Larmias\Command\Command;
use Larmias\Command\Contracts\ExceptionHandlerInterface;
use Larmias\ExceptionHandler\ExceptionHandler as BaseExceptionHandler;
use Throwable;
use function Larmias\Utils\println;
use function Larmias\Utils\format_exception;

class ExceptionHandler extends BaseExceptionHandler implements ExceptionHandlerInterface
{
    /**
     * @param Command $command
     * @param Throwable $e
     * @return int
     * @throws Throwable
     */
    public function render(Command $command, Throwable $e): int
    {
        $this->stopPropagation();
        println(format_exception($e));
        $command->exit();
        return $e->getCode();
    }

    /**
     * @param Throwable $e
     * @param mixed $result
     * @param mixed|null $args
     * @return int
     * @throws Throwable
     */
    public function handle(Throwable $e, mixed $result, mixed $args = null): int
    {
        return $this->render($args, $e);
    }
}