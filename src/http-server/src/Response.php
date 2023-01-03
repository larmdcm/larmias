<?php

declare(strict_types=1);

namespace Larmias\HttpServer;

use Larmias\Contracts\ContainerInterface;
use Larmias\Http\Message\Stream;
use Larmias\HttpServer\Contracts\ResponseInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Larmias\Utils\Codec\Json;
use Psr\Http\Message\StreamInterface;

class Response implements PsrResponseInterface, ResponseInterface
{
    public function __construct(protected ContainerInterface $container)
    {
    }

    /**
     * @param array|object $data
     * @return PsrResponseInterface
     */
    public function json(array|object $data): PsrResponseInterface
    {
        $json = Json::encode($data);
        return $this->withAddedHeader('content-type', 'application/json; charset=utf-8')
            ->withBody(Stream::create($json));
    }

    /**
     * @param string|\Stringable $data
     * @return PsrResponseInterface
     */
    public function raw(string|\Stringable $data): PsrResponseInterface
    {
        return $this->withAddedHeader('content-type', 'text/plain; charset=utf-8')
            ->withBody(Stream::create((string)$data));
    }

    protected function getResponse(): PsrResponseInterface
    {
        return $this->container->get(PsrResponseInterface::class);
    }

    protected function call(string $name, array $arguments): mixed
    {
        $response = $this->getResponse();
        if (!method_exists($response, $name)) {
            throw new \RuntimeException($name . ' Method not exist.');
        }
        return $response->{$name}(...$arguments);
    }

    public function getProtocolVersion(): string
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function withProtocolVersion($version): PsrResponseInterface
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function getHeaders(): array
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function hasHeader($name): bool
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function getHeader($name): array
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function getHeaderLine($name): string
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function withHeader($name, $value): PsrResponseInterface
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function withAddedHeader($name, $value): PsrResponseInterface
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function withoutHeader($name): PsrResponseInterface
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function getBody(): StreamInterface
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function withBody(StreamInterface $body): PsrResponseInterface
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function getStatusCode(): int
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function withStatus($code, $reasonPhrase = ''): PsrResponseInterface
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function getReasonPhrase(): string
    {
        return $this->call(__FUNCTION__, func_get_args());
    }
}