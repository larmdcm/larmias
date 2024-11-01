<?php

declare(strict_types=1);

namespace Larmias\HttpServer\Message;

use BadMethodCallException;
use Closure;
use Larmias\Codec\Xml;
use Larmias\Contracts\ContextInterface;
use Larmias\Http\Message\Contracts\Chunkable;
use Larmias\Http\Message\Cookie;
use Larmias\Http\Message\Exceptions\FileException;
use Larmias\Http\Message\Stream;
use Larmias\Http\Message\Stream\FileStream;
use Larmias\HttpServer\Contracts\ResponseInterface;
use Larmias\Codec\Json;
use Larmias\HttpServer\Contracts\SseEmitterInterface;
use Larmias\HttpServer\Contracts\SseResponseInterface;
use Larmias\HttpServer\Emitter\SseEmitter;
use Larmias\Support\MimeType;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Psr\Http\Message\StreamInterface;
use SplFileInfo;
use Stringable;
use function func_get_args;
use function get_class;
use function is_string;
use function Larmias\Support\value;
use function method_exists;
use function rawurlencode;
use function sprintf;

class Response implements PsrResponseInterface, ResponseInterface, SseResponseInterface
{
    /**
     * @var SseEmitterInterface|null
     */
    protected ?SseEmitterInterface $sseEmitter = null;

    /**
     * @param ContextInterface $context
     * @param PsrResponseInterface|null $response
     */
    public function __construct(protected ContextInterface $context, protected ?PsrResponseInterface $response = null)
    {
    }

    /**
     * @param array|object|string $data
     * @param string $charset
     * @return PsrResponseInterface
     */
    public function json(array|object|string $data, string $charset = 'utf-8'): PsrResponseInterface
    {
        $json = is_string($data) || $data instanceof Stringable ? (string)$data : Json::encode($data);
        return $this->withAddedHeader('Content-Type', 'application/json; charset=' . $charset)
            ->withBody(Stream::create($json));
    }

    /**
     * @param array|object|string $data
     * @param string $root
     * @param string $charset
     * @return PsrResponseInterface
     * @throws \Exception
     */
    public function xml(array|object|string $data, string $root = 'root', string $charset = 'utf-8'): PsrResponseInterface
    {
        $json = is_string($data) || $data instanceof Stringable ? (string)$data : Xml::toXml($data, root: $root);
        return $this->withAddedHeader('Content-Type', 'application/xml; charset=' . $charset)
            ->withBody(Stream::create($json));
    }

    /**
     * @param string|Stringable $data
     * @param string $charset
     * @return PsrResponseInterface
     */
    public function raw(string|Stringable $data, string $charset = 'utf-8'): PsrResponseInterface
    {
        return $this->withAddedHeader('Content-Type', 'text/plain; charset=' . $charset)
            ->withBody(Stream::create((string)$data));
    }

    /**
     * @param Stringable|string $data
     * @param string $charset
     * @return PsrResponseInterface
     */
    public function html(Stringable|string $data, string $charset = 'utf-8'): PsrResponseInterface
    {
        return $this->withAddedHeader('Content-Type', 'text/html; charset=' . $charset)
            ->withBody(Stream::create((string)$data));
    }

    /**
     * @param string|SplFileInfo $file
     * @return PsrResponseInterface
     */
    public function file(string|SplFileInfo $file): PsrResponseInterface
    {
        return $this->withBody(new FileStream($file));
    }

    /**
     * @param string|SplFileInfo $file
     * @param string $name
     * @return PsrResponseInterface
     */
    public function download(string|SplFileInfo $file, string $name = ''): PsrResponseInterface
    {
        if (is_string($file)) {
            $file = new SplFileInfo($file);
        }

        if (!$file->isReadable()) {
            throw new FileException('File must be readable.');
        }
        $filename = $name ?: $file->getBasename();
        $contentType = value(function () use ($file) {
            $mimeType = MimeType::fromExtension($file->getExtension());
            return $mimeType ?? 'application/octet-stream';
        });
        return $this->withHeader('content-description', 'File Transfer')
            ->withHeader('content-type', $contentType)
            ->withHeader('content-disposition', "attachment; filename=" . $filename . "; filename*=UTF-8''" . rawurlencode($filename))
            ->withHeader('content-transfer-encoding', 'binary')
            ->withHeader('pragma', 'public')
            ->withBody(new FileStream($file));
    }

    /**
     * @param string $url
     * @param int $status
     * @return PsrResponseInterface
     */
    public function redirect(string $url, int $status = 302): PsrResponseInterface
    {
        return $this->getResponse()->withStatus($status)->withAddedHeader('Location', $url);
    }

    /**
     * @param string $data
     * @return bool
     */
    public function write(string $data): bool
    {
        $response = $this->getResponse();
        if ($response instanceof Chunkable) {
            return $response->write($data);
        }
        return false;
    }

    /**
     * @param Closure $handler
     * @return PsrResponseInterface
     */
    public function sse(Closure $handler): PsrResponseInterface
    {
        $response = new static($this->context, $this->getResponse());
        $response->sseEmitter = new SseEmitter($handler);
        return $response;
    }

    /**
     * @return SseEmitterInterface|null
     */
    public function getSseEmitter(): ?SseEmitterInterface
    {
        return $this->sseEmitter;
    }

    protected function getResponse(): PsrResponseInterface
    {
        if ($this->response instanceof PsrResponseInterface) {
            return $this->response;
        }
        return $this->context->get(PsrResponseInterface::class);
    }

    protected function call(string $name, array $arguments): static
    {
        $response = $this->getResponse();
        if (!method_exists($response, $name)) {
            throw new BadMethodCallException(sprintf('Call to undefined method %s::%s()', get_class($this), $name));
        }
        return new static($this->context, $response->{$name}(...$arguments));
    }

    /**
     * @param Cookie $cookie
     * @return ResponseInterface
     */
    public function withCookie(Cookie $cookie): ResponseInterface
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    /**
     * @return array
     */
    public function getCookies(): array
    {
        return $this->getResponse()->getCookies();
    }

    public function getProtocolVersion(): string
    {
        return $this->getResponse()->getProtocolVersion();
    }

    public function withProtocolVersion($version): PsrResponseInterface
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function getHeaders(): array
    {
        return $this->getResponse()->getHeaders();
    }

    public function hasHeader($name): bool
    {
        return $this->getResponse()->hasHeader($name);
    }

    public function getHeader($name): array
    {
        return $this->getResponse()->getHeader($name);
    }

    public function getHeaderLine($name): string
    {
        return $this->getResponse()->getHeaderLine($name);
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
        return $this->getResponse()->getBody();
    }

    public function withBody(StreamInterface $body): PsrResponseInterface
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function getStatusCode(): int
    {
        return $this->getResponse()->getStatusCode();
    }

    public function withStatus($code, $reasonPhrase = ''): PsrResponseInterface
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function getReasonPhrase(): string
    {
        return $this->getResponse()->getReasonPhrase();
    }

    public function __call($name, $arguments)
    {
        $response = $this->getResponse();
        if (!method_exists($response, $name)) {
            throw new BadMethodCallException(sprintf('Call to undefined method %s::%s()', get_class($this), $name));
        }
        return $response->{$name}(...$arguments);
    }
}