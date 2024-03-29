<?php

declare(strict_types=1);

namespace Larmias\Engine\WorkerMan\Http;

use Larmias\Contracts\Http\RequestInterface;
use Workerman\Protocols\Http\Request as WorkerRequest;
use RuntimeException;
use function strtolower;

class Request implements RequestInterface
{
    /**
     * @param WorkerRequest $request
     */
    public function __construct(protected WorkerRequest $request)
    {
    }

    /**
     * get 'Header' request data.
     *
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    public function header(?string $key = null, mixed $default = null): mixed
    {
        return $this->request->header($key, $default);
    }

    /**
     * get 'Get' request data.
     *
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    public function query(?string $key = null, mixed $default = null): mixed
    {
        return $this->request->get($key, $default);
    }

    /**
     * get 'Post' request data.
     *
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    public function post(?string $key = null, mixed $default = null): mixed
    {
        return $this->request->post($key, $default);
    }

    /**
     * @param string|null $key
     * @param mixed|null $default
     * @return mixed
     */
    public function server(?string $key = null, mixed $default = null): mixed
    {
        $server = [];
        foreach ($_SERVER as $name => $value) {
            $server[strtolower($name)] = $value;
        }

        if ($key === null) {
            return $server;
        }
        return $server[$key] ?? $default;
    }

    /**
     * get Upload files.
     *
     * @param string|null $name
     * @return array|null
     */
    public function file(?string $name = null): ?array
    {
        return $this->request->file($name);
    }

    /**
     * get http raw header.
     *
     * @return string
     */
    public function rawHeader(): string
    {
        return $this->request->rawHead();
    }

    /**
     * Get http raw body.
     *
     * @return string
     */
    public function rawBody(): string
    {
        return $this->request->rawBody();
    }

    /**
     * Get request method.
     *
     * @return string
     */
    public function method(): string
    {
        return $this->request->method();
    }

    /**
     * Get request uri.
     *
     * @return string
     */
    public function uri(): string
    {
        return $this->request->uri();
    }

    /**
     * Get request schema.
     *
     * @return string
     */
    public function schema(): string
    {
        return $this->request->connection->transport === 'ssl' ? 'https' : 'http';
    }

    /**
     * Get request query string.
     *
     * @return string
     */
    public function queryString(): string
    {
        return $this->request->queryString();
    }

    /**
     * Get 'path info'.
     *
     * @return string
     */
    public function path(): string
    {
        return $this->request->path();
    }

    /**
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    public function cookie(?string $key = null, mixed $default = null): mixed
    {
        return $this->request->cookie($key, $default);
    }

    /**
     * @param string|null $key
     * @param mixed|null $default
     * @return mixed
     */
    public function session(?string $key = null, mixed $default = null): mixed
    {
        throw new RuntimeException('session not implement.');
    }

    /**
     * @return string
     */
    public function protocolVersion(): string
    {
        return $this->request->protocolVersion();
    }
}