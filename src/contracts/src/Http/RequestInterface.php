<?php

declare(strict_types=1);

namespace Larmias\Contracts\Http;

interface RequestInterface
{
    /**
     * get 'Header' request data.
     *
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    public function header(?string $key = null, $default = null): mixed;

    /**
     * get 'Get' request data.
     *
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    public function query(?string $key = null, $default = null): mixed;

    /**
     * get 'Post' request data.
     *
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    public function post(?string $key = null, $default = null): mixed;

    /**
     * get Upload files.
     *
     * @param string|null $name
     * @return array|null
     */
    public function file(?string $name = null): ?array;

    /**
     * get http raw header.
     *
     * @return string
     */
    public function rawHeader(): string;

    /**
     * Get http raw body.
     *
     * @return string
     */
    public function rawBody(): string;

    /**
     * Get request method.
     *
     * @return string
     */
    public function method(): string;

    /**
     * Get request uri.
     *
     * @return string
     */
    public function uri(): string;

    /**
     * Get request schema.
     *
     * @return string
     */
    public function schema(): string;

    /**
     * Get request query string.
     *
     * @return string
     */
    public function queryString(): string;

    /**
     * Get 'path info'.
     *
     * @return string
     */
    public function getPathInfo(): string;
}