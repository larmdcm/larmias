<?php

declare(strict_types=1);

namespace Larmias\Coroutine\Exceptions;

use Throwable;

class ExceptionThrower
{
    public function __construct(public Throwable $throwable)
    {
    }

    public function getThrowable(): Throwable
    {
        return $this->throwable;
    }
}