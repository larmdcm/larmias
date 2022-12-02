<?php

declare(strict_types=1);

namespace Larmias\Engine\WorkerS\Http;

use Larmias\Contracts\Http\ResponseInterface;
use Larmias\WorkerS\Protocols\Http\Response as WorkerResponse;

class Response implements ResponseInterface
{
    /**
     * Request constructor.
     * @param \Larmias\WorkerS\Protocols\Http\Response $response
     */
    public function __construct(protected WorkerResponse $response)
    {
    }

    public function header(string $name, mixed $value): ResponseInterface
    {
        $this->response->header($name, $value);
        return $this;
    }

    public function withHeaders(array $headers): ResponseInterface
    {
        $this->response->withHeaders($headers);
        return $this;
    }

    public function withoutHeader(string $name): ResponseInterface
    {
        $this->response->withoutHeader($name);
        return $this;
    }

    public function hasHeader(string $name): bool
    {
        return $this->response->hasHeader($name);
    }

    public function getHeader(string $name, $default = null): string|array
    {
        return $this->response->getHeader($name,$default);
    }

    public function getHeaders(): array
    {
        return $this->response->getHeaders();
    }

    public function status(int $statusCode, ?string $reason = null): ResponseInterface
    {
        $this->response->status($statusCode,$reason);
        return $this;
    }

    public function getStatusCode(): int
    {
        return $this->response->getStatusCode();
    }

    public function getReason(): ?string
    {
        return $this->response->getReason();
    }

    public function write(string $data): ResponseInterface
    {
        $this->response->write($data);
        return $this;
    }

    public function sendFile(string $file, int $offset = 0,int $length = 0): void
    {
        $this->response->sendFile($file,$offset,$length);
    }

    public function end(string $data = ''): void
    {
        $this->response->end($data);
    }
}