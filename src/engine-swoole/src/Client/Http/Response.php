<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole\Client\Http;

use Larmias\Contracts\Client\Http\ResponseInterface;

class Response implements ResponseInterface
{
    public function __construct(protected int $statusCode, protected array $headers, protected ?string $body, protected string $version = '1.1')
    {
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function getVersion(): string
    {
        return $this->version;
    }
}