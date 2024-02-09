<?php

declare(strict_types=1);

namespace Larmias\Contracts\Client;

use RuntimeException;
use Throwable;

class ClientException extends RuntimeException
{
    public function __construct(string $message = "", protected int $errCode = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }

    public function getErrCode(): int
    {
        return $this->errCode;
    }
}