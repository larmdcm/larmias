<?php

declare(strict_types=1);

namespace Larmias\VarDumper\Handler;

use Larmias\ExceptionHandler\ExceptionHandler as BaseExceptionHandler;
use Larmias\VarDumper\Exceptions\VarDumperException;
use Throwable;

class VarDumperExceptionHandler extends BaseExceptionHandler
{
    /**
     * @param Throwable $e
     * @param mixed $result
     * @param mixed|null $args
     * @return mixed
     */
    public function handle(Throwable $e, mixed $result, mixed $args = null): mixed
    {
        return $result;
    }

    /**
     * @param Throwable $e
     * @return bool
     */
    public function isValid(Throwable $e): bool
    {
        return $e instanceof VarDumperException;
    }
}