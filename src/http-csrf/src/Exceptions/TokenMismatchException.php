<?php

declare(strict_types=1);

namespace Larmias\Http\CSRF\Exceptions;

use Larmias\HttpServer\Exceptions\HttpException;

class TokenMismatchException extends HttpException
{
    /**
     * @param string $message
     * @param int $statusCode
     */
    public function __construct(string $message = 'Token verification error', int $statusCode = 500)
    {
        parent::__construct($message, $statusCode);
    }
}