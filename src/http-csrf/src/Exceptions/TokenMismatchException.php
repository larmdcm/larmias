<?php

declare(strict_types=1);

namespace Larmias\Http\CSRF\Exceptions;

use Larmias\HttpServer\Exceptions\HttpException;
use Throwable;

class TokenMismatchException extends HttpException
{
    public function __construct(string $message = 'Token verification error', int $statusCode = 500, Throwable $previous = null)
    {
        parent::__construct($statusCode, $message, $previous);
    }
}