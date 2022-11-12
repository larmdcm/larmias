<?php

declare(strict_types=1);

namespace Larmias\Engine\Drivers\WorkerS\Http;

use Larmias\Contracts\Http\RequestInterface;
use Larmias\WorkerS\Protocols\Http\Request as WorkerRequest;

class Request implements RequestInterface
{
    /**
     * Request constructor.
     * @param \Larmias\WorkerS\Protocols\Http\Request $request
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
    public function header(?string $key = null, $default = null): mixed
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
    public function query(?string $key = null, $default = null): mixed
    {
        return $this->request->query($key, $default);
    }

    /**
     * get 'Post' request data.
     *
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    public function post(?string $key = null, $default = null): mixed
    {
        return $this->request->post($key, $default);
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
        return $this->request->rawHeader();
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
        return $this->request->schema();
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
    public function getPathInfo(): string
    {
        return $this->request->getPathInfo();
    }
}