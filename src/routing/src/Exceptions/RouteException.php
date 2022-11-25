<?php

declare(strict_types=1);

namespace Larmias\Routing\Exceptions;

use RuntimeException;
use Throwable;

class RouteException extends RuntimeException
{
    /** @var int */
    protected int $statusCode = 400;

    /**
     * RouteException constructor.
     * @param string $message
     * @param int $statusCode
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(string $message = 'resource delete fail', int $statusCode = -1, int $code = 0, Throwable $previous = null)
    {
        $this->statusCode = $statusCode;
        parent::__construct($message, $code, $previous);
    }
}