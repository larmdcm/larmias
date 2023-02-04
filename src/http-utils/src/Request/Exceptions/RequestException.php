<?php

declare(strict_types=1);

namespace Larmias\Http\Utils\Request\Exceptions;

use Psr\Http\Client\RequestExceptionInterface;
use Psr\Http\Message\RequestInterface;
use RuntimeException;
use Throwable;

class RequestException extends RuntimeException implements RequestExceptionInterface
{
    public function __construct(protected RequestInterface $request, string $message = "request exception.", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }
}