<?php

declare(strict_types=1);

namespace Larmias\HttpServer;

use Larmias\Contracts\ContainerInterface;
use Larmias\Http\Message\Stream;
use Larmias\HttpServer\Contracts\ResponseInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Larmias\Contracts\Http\ResponseInterface as HttpResponseInterface;
use Larmias\Utils\Codec\Json;
use Psr\Http\Message\StreamInterface;

class Response implements PsrResponseInterface, ResponseInterface
{
    public function __construct(protected ContainerInterface $container)
    {
    }

    /**
     * @param array|object $data
     * @return \Larmias\HttpServer\Contracts\ResponseInterface
     */
    public function json(array|object $data): ResponseInterface
    {
        $json = Json::encode($data);
        return $this->withAddedHeader('Content-Type', 'application/json; charset=utf-8')
            ->withBody(Stream::create($json));
    }

    /**
     * @param string|\Stringable $data
     * @return \Larmias\HttpServer\Contracts\ResponseInterface
     */
    public function raw(string|\Stringable $data): ResponseInterface
    {
        return $this->withAddedHeader('Content-Type', 'text/plain; charset=utf-8')
            ->withBody(Stream::create((string)$data));
    }

    /**
     * @return void
     */
    public function send(): void
    {
        /** @var HttpResponseInterface $response */
        $response = $this->container->get(HttpResponseInterface::class);
        $response->withHeaders($this->getHeaders())
            ->status($this->getStatusCode(), $this->getReasonPhrase())
            ->end((string)$this->getBody());
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
        $result = $response->{$name}(...$arguments);
        if ($result instanceof PsrResponseInterface) {
            $this->container->instance(PsrResponseInterface::class, $result);
            return $this;
        }
        return $result;
    }

    public function getProtocolVersion(): string
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function withProtocolVersion($version): self
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

    public function withHeader($name, $value): self
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function withAddedHeader($name, $value): self
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function withoutHeader($name): self
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function getBody(): StreamInterface
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function withBody(StreamInterface $body): self
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function getStatusCode(): int
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function withStatus($code, $reasonPhrase = ''): self
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function getReasonPhrase(): string
    {
        return $this->call(__FUNCTION__, func_get_args());
    }
}