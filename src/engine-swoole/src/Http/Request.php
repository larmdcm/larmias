<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole\Http;

use Larmias\Contracts\Http\RequestInterface;
use Swoole\Http\Request as SwooleRequest;
use function Larmias\Utils\data_get;
use function strstr;
use function strtolower;
use function explode;

class Request implements RequestInterface
{
    /**
     * @param SwooleRequest $request
     */
    public function __construct(protected SwooleRequest $request)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function header(?string $key = null, mixed $default = null): mixed
    {
        return data_get($this->request->header, $key, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function query(?string $key = null, mixed $default = null): mixed
    {
        return data_get($this->request->get, $key, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function post(?string $key = null, mixed $default = null): mixed
    {
        return data_get($this->request->post, $key, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function server(?string $key = null, mixed $default = null): mixed
    {
        return data_get($this->request->server, $key, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function cookie(?string $key = null, mixed $default = null): mixed
    {
        return data_get($this->request->cookie, $key, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function session(?string $key = null, mixed $default = null): mixed
    {
        throw new \RuntimeException(__FUNCTION__ . ' not implement.');
    }

    /**
     * {@inheritdoc}
     */
    public function file(?string $name = null): ?array
    {
        return $name ? ($this->request->files[$name] ?? null) : $this->request->files;
    }

    /**
     * {@inheritdoc}
     */
    public function rawHeader(): string
    {
        return strstr($this->request->getData(), "\r\n\r\n", true);
    }

    /**
     * {@inheritdoc}
     */
    public function rawBody(): string
    {
        return $this->request->getContent();
    }

    /**
     * {@inheritdoc}
     */
    public function method(): string
    {
        return $this->request->getMethod();
    }

    /**
     * {@inheritdoc}
     */
    public function uri(): string
    {
        $queryString = $this->queryString();
        return $this->path() . (empty($queryString) ? '' : '?' . $queryString);
    }

    /**
     * {@inheritdoc}
     */
    public function schema(): string
    {
        $serverProtocol = $this->server('server_protocol');
        if (!$serverProtocol) return '';
        return strtolower(explode('/', $serverProtocol)[0]);
    }

    /**
     * {@inheritdoc}
     */
    public function protocolVersion(): string
    {
        $serverProtocol = $this->server('server_protocol');
        if (!$serverProtocol) return '';
        return strtolower(explode('/', $serverProtocol)[1]);
    }

    /**
     * {@inheritdoc}
     */
    public function queryString(): string
    {
        return $this->server('query_string', '');
    }

    /**
     * {@inheritdoc}
     */
    public function path(): string
    {
        return $this->server('request_uri', '');
    }
}