<?php

declare(strict_types=1);

namespace Larmias\ShareMemory\Exceptions;

use Throwable;
use RuntimeException;

class ClientException extends RuntimeException
{
    public function __construct(string $message = "", int $errCode = 0, Throwable $previous = null)
    {
        parent::__construct($message, $errCode, $previous);
    }
}