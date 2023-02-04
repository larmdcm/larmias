<?php

declare(strict_types=1);

namespace Larmias\SharedMemory\Exceptions;

use RuntimeException;
use Throwable;

class ServerException extends RuntimeException
{
    public function __construct(string $message = "", int $errCode = 0, Throwable $previous = null)
    {
        parent::__construct($message, $errCode, $previous);
    }
}