<?php

declare(strict_types=1);

namespace Larmias\Throttle\Exceptions;

use RuntimeException;
use Throwable;

class RateLimitOfErrorException extends RuntimeException
{
    /**
     * @var int
     */
    protected int $waitSeconds = 0;

    public function __construct(string $message = "Too Many Requests", protected int $statusCode = 429, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return int
     */
    public function getWaitSeconds(): int
    {
        return $this->waitSeconds;
    }

    /**
     * @param int $waitSeconds
     */
    public function setWaitSeconds(int $waitSeconds): void
    {
        $this->waitSeconds = $waitSeconds;
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}