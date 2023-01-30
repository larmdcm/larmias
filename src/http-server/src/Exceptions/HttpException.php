<?php

declare(strict_types=1);

namespace Larmias\HttpServer\Exceptions;

use RuntimeException;
use Throwable;

class HttpException extends RuntimeException
{
    /**
     * @var int
     */
    protected int $statusCode = 500;

    /**
     * @var array
     */
    protected array $headers = [];

    /**
     * HttpException constructor.
     *
     * @param int $statusCode
     * @param string $message
     * @param array $headers
     * @param int $code
     */
    public function __construct(int $statusCode = 500, string $message = 'http exception.', Throwable $previous = null, array $headers = [], $code = 0)
    {
        $this->statusCode = $statusCode;
        $this->headers = $headers;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }
}