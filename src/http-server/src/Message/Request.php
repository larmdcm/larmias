<?php

declare(strict_types=1);

namespace Larmias\HttpServer\Message;

use Larmias\Contracts\ContextInterface;
use Larmias\HttpServer\Contracts\RequestInterface;
use Larmias\Http\Message\UploadedFile;
use Larmias\Routing\Dispatched;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Larmias\Utils\Arr;
use SplFileInfo;
use function Larmias\Utils\data_get;
use function Larmias\Utils\value;

class Request implements RequestInterface
{
    /**
     * @param ContextInterface $context
     */
    public function __construct(protected ContextInterface $context)
    {
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get(string $name): mixed
    {
        return $this->context->get(__CLASS__ . '.properties.' . $name);
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function __set(string $name, mixed $value)
    {
        $this->context->set(__CLASS__ . '.properties.' . $name, value($value));
    }

    /**
     * @param string|null $key
     * @param mixed|null $default
     * @return mixed
     */
    public function query(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->getQueryParams();
        }
        return data_get($this->getQueryParams(), $key, $default);
    }

    /**
     * @param string|null $key
     * @param mixed|null $default
     * @return mixed
     */
    public function post(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->getParsedBody();
        }
        return data_get($this->getParsedBody(), $key, $default);
    }

    /**
     * @param string|null $key
     * @param null $default
     * @return mixed
     */
    public function route(?string $key = null, mixed $default = null): mixed
    {
        $route = $this->getAttribute(Dispatched::class);
        if (\is_null($route)) {
            return $default;
        }
        if (\is_null($key)) {
            return $route->params;
        }
        return \array_key_exists($key, $route->params) ? $route->params[$key] : $default;
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function input(string $key, mixed $default = null): mixed
    {
        $data = $this->getInputData();

        return data_get($data, $key, $default);
    }

    /**
     * @param array $keys
     * @param null $default
     * @return array
     */
    public function inputs(array $keys, mixed $default = null): array
    {
        $data = $this->getInputData();
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = data_get($data, $key, $default[$key] ?? null);
        }

        return $result;
    }

    /**
     * Retrieve a file from the request.
     *
     * @param null|mixed $default
     * @return null|UploadedFile|UploadedFile[]
     */
    public function file(string $key, $default = null)
    {
        return Arr::get($this->getUploadedFiles(), $key, $default);
    }

    /**
     * Determine if the uploaded data contains a file.
     */
    public function hasFile(string $key): bool
    {
        if ($file = $this->file($key)) {
            return $this->isValidFile($file);
        }
        return false;
    }

    /**
     * Determine if the $keys is exist in parameters.
     *
     * @param array|string $keys
     */
    public function has(array|string $keys): bool
    {
        return Arr::has($this->getInputData(), $keys);
    }

    /**
     * Retrieve the data from request headers.
     *
     * @param mixed $default
     * @return mixed
     */
    public function header(string $key, mixed $default = null): mixed
    {
        if (!$this->hasHeader($key)) {
            return $default;
        }
        return $this->getHeaderLine($key);
    }

    /**
     * Retrieve a cookie from the request.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function cookie(string $key, mixed $default = null): mixed
    {
        return data_get($this->getCookieParams(), $key, $default);
    }

    /**
     * @param string $key
     * @return bool
     */
    public function hasCookie(string $key): bool
    {
        return !\is_null($this->cookie($key));
    }

    /**
     * @param string $key
     * @param null $default
     * @return mixed
     */
    public function server(string $key, $default = null): mixed
    {
        return data_get($this->getServerParams(), $key, $default);
    }

    /**
     * @param string $method
     * @return bool
     */
    public function isMethod(string $method): bool
    {
        return $this->getMethod() === strtoupper($method);
    }

    /**
     * Get the current path info for the request.
     *
     * @return string
     */
    public function path(): string
    {
        $pattern = trim($this->getPathInfo(), '/');

        return $pattern == '' ? '/' : $pattern;
    }

    /**
     * @return string
     */
    public function getPathInfo(): string
    {
        $requestUri = $this->getRequestTarget();

        // Remove the query string from REQUEST_URI
        if (false !== $pos = strpos($requestUri, '?')) {
            $requestUri = substr($requestUri, 0, $pos);
        }
        if ($requestUri !== '' && $requestUri[0] !== '/') {
            $requestUri = '/' . $requestUri;
        }

        return $requestUri;
    }

    /**
     * Get the URL (no query string) for the request.
     * @return string
     */
    public function url(): string
    {
        return \rtrim(\preg_replace('/\?.*/', '', (string)$this->getUri()), '/');
    }

    /**
     * Get the full URL for the request.
     */
    public function fullUrl(): string
    {
        $query = $this->getQueryString();

        return $this->url() . '?' . $query;
    }

    /**
     * Generates the normalized query string for the Request.
     *
     * It builds a normalized query string, where keys/value pairs are alphabetized
     * and have consistent escaping.
     *
     * @return string A normalized query string for the Request
     */
    public function getQueryString(): string
    {
        return static::normalizeQueryString($this->getUri()->getQuery());
    }

    /**
     * Normalizes a query string.
     *
     * It builds a normalized query string, where keys/value pairs are alphabetized,
     * have consistent escaping and unneeded delimiters are removed.
     *
     * @param string $qs Query string
     * @return string A normalized query string for the Request
     */
    public function normalizeQueryString(string $qs): string
    {
        if ($qs == '') {
            return '';
        }

        parse_str($qs, $qsData);
        ksort($qsData);

        return http_build_query($qsData, '', '&', PHP_QUERY_RFC3986);
    }

    /**
     * @return array
     */
    public function all(): array
    {
        return $this->getInputData();
    }

    /**
     * @return array
     */
    protected function getInputData(): array
    {
        $body = $this->getParsedBody();
        $route = $this->route();
        return \array_merge(\is_array($route) ? $route : [], $this->getQueryParams(), \is_array($body) ? $body : []);
    }

    /**
     * Check that the given file is a valid SplFileInfo instance.
     * @param mixed $file
     */
    protected function isValidFile(mixed $file): bool
    {
        return $file instanceof SplFileInfo && $file->getPath() !== '';
    }

    /**
     * @return ServerRequestInterface
     */
    protected function getRequest(): ServerRequestInterface
    {
        return $this->context->get(ServerRequestInterface::class);
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    protected function call(string $name, array $arguments): mixed
    {
        $request = $this->getRequest();
        if (!\method_exists($request, $name)) {
            throw new \RuntimeException($name . ' Method not exist.');
        }
        return $request->{$name}(...$arguments);
    }

    public function getProtocolVersion(): string
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function withProtocolVersion($version): ServerRequestInterface
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

    public function withHeader($name, $value): ServerRequestInterface
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function withAddedHeader($name, $value): ServerRequestInterface
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function withoutHeader($name): ServerRequestInterface
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function getBody(): StreamInterface
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function withBody(StreamInterface $body): ServerRequestInterface
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function getRequestTarget(): string
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function withRequestTarget($requestTarget): ServerRequestInterface
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function getMethod(): string
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function withMethod($method): ServerRequestInterface
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function getUri(): UriInterface
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function withUri(UriInterface $uri, $preserveHost = false): ServerRequestInterface
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function getServerParams(): array
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function getCookieParams()
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function withCookieParams(array $cookies): ServerRequestInterface
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function getQueryParams(): array
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function withQueryParams(array $query): ServerRequestInterface
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function getUploadedFiles(): array
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function withUploadedFiles(array $uploadedFiles): ServerRequestInterface
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function getParsedBody(): mixed
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function withParsedBody($data): ServerRequestInterface
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function getAttributes(): array
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function getAttribute($name, $default = null): mixed
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function withAttribute($name, $value): ServerRequestInterface
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function withoutAttribute($name): ServerRequestInterface
    {
        return $this->call(__FUNCTION__, func_get_args());
    }
}