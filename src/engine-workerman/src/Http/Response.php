<?php

declare(strict_types=1);

namespace Larmias\Engine\WorkerMan\Http;

use Larmias\Contracts\Http\ResponseInterface;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Response as WorkerResponse;

class Response implements ResponseInterface
{
    /**
     * @param TcpConnection $connection
     * @param WorkerResponse|null $response
     */
    public function __construct(protected TcpConnection $connection,protected ?WorkerResponse $response = null)
    {
        if (\is_null($this->response)) {
            $this->response = new WorkerResponse();
        }
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
        return isset($this->response->getHeaders()[$name]);
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
        $this->response->withStatus($statusCode,$reason);
        return $this;
    }

    public function getStatusCode(): int
    {
        return $this->response->getStatusCode();
    }

    public function getReason(): ?string
    {
        return $this->response->getReasonPhrase();
    }

    public function write(string $data): ResponseInterface
    {
        return $this;
    }

    public function sendFile(string $file, int $offset = 0,int $length = 0): void
    {
        $this->response->withFile($file,$offset,$length);
        $this->end();
    }

    public function end(string $data = ''): void
    {
        if (!empty($data)) {
            $this->response->withBody($data);
        }
        $this->connection->send($this->response);
    }
}