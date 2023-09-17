<?php

declare(strict_types=1);

namespace Larmias\HttpServer\Contracts;

use Psr\Http\Message\ServerRequestInterface;
use Larmias\Http\Message\UploadedFile;

interface RequestInterface extends ServerRequestInterface
{
    /**
     * @param string|null $key
     * @param mixed|null $default
     * @return mixed
     */
    public function query(?string $key = null, mixed $default = null): mixed;

    /**
     * @param string|null $key
     * @param mixed|null $default
     * @return mixed
     */
    public function post(?string $key = null, mixed $default = null): mixed;

    /**
     * @param string|null $key
     * @param null $default
     * @return mixed
     */
    public function route(?string $key = null, mixed $default = null): mixed;

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function input(string $key, mixed $default = null): mixed;

    /**
     * @param array $keys
     * @param array $default
     * @return array
     */
    public function inputs(array $keys, array $default = []): array;

    /**
     * Retrieve a file from the request.
     *
     * @param null|mixed $default
     * @return null|UploadedFile|UploadedFile[]
     */
    public function file(string $key, $default = null);

    /**
     * Determine if the uploaded data contains a file.
     */
    public function hasFile(string $key): bool;

    /**
     * Determine if the $keys is exist in parameters.
     *
     * @param array|string $keys
     */
    public function has(array|string $keys): bool;

    /**
     * Retrieve the data from request headers.
     *
     * @param mixed $default
     * @return mixed
     */
    public function header(string $key, mixed $default = null): mixed;

    /**
     * Retrieve a cookie from the request.
     * @param null|mixed $default
     * @return mixed
     */
    public function cookie(string $key, mixed $default = null): mixed;

    /**
     * @param string $key
     * @return bool
     */
    public function hasCookie(string $key): bool;

    /**
     * @param string $key
     * @param null $default
     * @return mixed
     */
    public function server(string $key, $default = null): mixed;

    /**
     * @param string $method
     * @return bool
     */
    public function isMethod(string $method): bool;

    /**
     * Get the current path info for the request.
     *
     * @return string
     */
    public function path(): string;

    /**
     * @return string
     */
    public function getPathInfo(): string;

    /**
     * Get the URL (no query string) for the request.
     * @return string
     */
    public function url(): string;

    /**
     * Get the full URL for the request.
     */
    public function fullUrl(): string;

    /**
     * Generates the normalized query string for the Request.
     *
     * It builds a normalized query string, where keys/value pairs are alphabetized
     * and have consistent escaping.
     *
     * @return string A normalized query string for the Request
     */
    public function getQueryString(): string;

    /**
     * @return array
     */
    public function all(): array;
}