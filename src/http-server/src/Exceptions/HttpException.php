<?php

declare(strict_types=1);

namespace Larmias\HttpServer\Exceptions;

use RuntimeException;

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
     * @param string $message
     * @param int $statusCode
     * @param array $headers
     */
    public function __construct(string $message = 'http exception.', int $statusCode = 500, array $headers = [])
    {
        $this->statusCode = $statusCode;
        $this->headers = $headers;
        parent::__construct($message);
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