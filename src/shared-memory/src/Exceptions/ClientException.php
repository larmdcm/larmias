<?php

declare(strict_types=1);

namespace Larmias\SharedMemory\Exceptions;

use Throwable;
use RuntimeException;

class ClientException extends RuntimeException
{
    /**
     * @param string $message
     * @param int $errCode
     * @param Throwable|null $previous
     */
    public function __construct(string $message = "", int $errCode = 0, Throwable $previous = null)
    {
        parent::__construct($message, $errCode, $previous);
    }
}