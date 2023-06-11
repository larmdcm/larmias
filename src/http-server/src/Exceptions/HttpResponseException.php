<?php

declare(strict_types=1);

namespace Larmias\HttpServer\Exceptions;

use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Throwable;

class HttpResponseException extends RuntimeException
{
    /**
     * HttpResponseException constructor.
     *
     * @param ResponseInterface $response
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(protected ResponseInterface $response, string $message = "http response exception.", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }
}