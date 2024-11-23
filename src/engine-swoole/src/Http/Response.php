<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole\Http;

use Larmias\Contracts\Http\ResponseInterface;
use Swoole\Http\Response as SwooleResponse;
use BadMethodCallException;
use function is_array;

class Response implements ResponseInterface
{
    /**
     * @param SwooleResponse $response
     */
    public function __construct(protected SwooleResponse $response)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function header(string $name, mixed $value): ResponseInterface
    {
        $this->response->header($name, $value);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function withHeaders(array $headers): ResponseInterface
    {
        foreach ($headers as $name => $value) {
            if (is_array($value)) {
                foreach ($value as $item) {
                    $this->header($name, $item);
                }
            } else {
                $this->header($name, $value);
            }
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function withoutHeader(string $name): ResponseInterface
    {
        throw new BadMethodCallException(__FUNCTION__ . ' not implement.');
    }

    /**
     * {@inheritdoc}
     */
    public function hasHeader(string $name): bool
    {
        throw new BadMethodCallException(__FUNCTION__ . ' not implement.');
    }

    /**
     * {@inheritdoc}
     */
    public function getHeader(string $name, $default = null): string|array
    {
        throw new BadMethodCallException(__FUNCTION__ . ' not implement.');
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaders(): array
    {
        throw new BadMethodCallException(__FUNCTION__ . ' not implement.');
    }

    /**
     * {@inheritdoc}
     */
    public function status(int $statusCode, ?string $reason = null): ResponseInterface
    {
        $this->response->status($statusCode, $reason);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getStatusCode(): int
    {
        throw new BadMethodCallException(__FUNCTION__ . ' not implement.');
    }

    /**
     * {@inheritdoc}
     */
    public function getReason(): ?string
    {
        throw new BadMethodCallException(__FUNCTION__ . ' not implement.');
    }

    /**
     * {@inheritdoc}
     */
    public function cookie(
        string $name, string $value = '', int $expire = 0, string $path = '/', string $domain = '',
        bool   $secure = false, bool $httpOnly = true, ?string $sameSite = null
    ): ResponseInterface
    {
        $this->response->cookie($name, $value, $expire, $path, $domain, $secure, $httpOnly, $sameSite ?: '');
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function write(string $data): bool
    {
        $this->response->write($data);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function sendFile(string $file, int $offset = 0, int $length = 0): void
    {
        $this->response->sendfile($file, $offset, $length);
    }

    /**
     * {@inheritdoc}
     */
    public function end(string $data = ''): void
    {
        $this->response->end($data);
    }

    /**
     * @return SwooleResponse
     */
    public function getSwooleResponse(): SwooleResponse
    {
        return $this->response;
    }
}