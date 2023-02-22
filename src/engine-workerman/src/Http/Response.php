<?php

declare(strict_types=1);

namespace Larmias\Engine\WorkerMan\Http;

use Larmias\Contracts\Http\ResponseInterface;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Chunk;
use Workerman\Protocols\Http\Response as WorkerResponse;

class Response implements ResponseInterface
{
    /**
     * @var bool
     */
    protected bool $isSendChunk = false;

    /**
     * @param TcpConnection $connection
     * @param WorkerResponse|null $response
     */
    public function __construct(protected TcpConnection $connection, protected ?WorkerResponse $response = null)
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
        return $this->response->getHeader($name) ?? $default;
    }

    public function getHeaders(): array
    {
        return $this->response->getHeaders();
    }

    public function status(int $statusCode, ?string $reason = null): ResponseInterface
    {
        $this->response->withStatus($statusCode, $reason);
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

    public function cookie(string $name, string $value = '', int $expire = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httpOnly = true, ?string $sameSite = null): self
    {
        $maxAge = $expire !== 0 ? $expire - time() : null;
        $this->response->cookie($name, $value, $maxAge, $path, $domain, $secure, $httpOnly, $sameSite);
        return $this;
    }

    public function write(string $data): bool
    {
        if ($this->isSendChunk) {
            $this->connection->send(new Chunk($data));
        } else {
            $this->response->withHeader('Transfer-Encoding', 'chunked');
            $this->response->withBody($data);
            $this->connection->send($this->response);
        }
        $this->isSendChunk = true;
        return true;
    }

    public function sendFile(string $file, int $offset = 0, int $length = 0): void
    {
        $this->response->withFile($file, $offset, $length);
        $this->end();
    }

    public function end(string $data = ''): void
    {
        if ($this->isSendChunk) {
            $this->connection->send(new Chunk($data));
            return;
        }
        if (!empty($data)) {
            $this->response->withBody($data);
        }
        $this->connection->send($this->response);
    }
}