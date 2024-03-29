<?php

declare(strict_types=1);

namespace Larmias\HttpServer\Contracts;

use Larmias\Http\Message\Cookie;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

interface ResponseInterface
{
    /**
     * @param array|object|string $data
     * @param int $code
     * @param array $headers
     * @return PsrResponseInterface
     */
    public function json(array|object|string $data, int $code = 200, array $headers = []): PsrResponseInterface;

    /**
     * @param string|\Stringable $data
     * @param int $code
     * @param array $headers
     * @return PsrResponseInterface
     */
    public function raw(string|\Stringable $data, int $code = 200, array $headers = []): PsrResponseInterface;

    /**
     * @param string|\Stringable $data
     * @param int $code
     * @param array $headers
     * @return PsrResponseInterface
     */
    public function html(string|\Stringable $data, int $code = 200, array $headers = []): PsrResponseInterface;

    /**
     * @param string|\SplFileInfo $file
     * @return PsrResponseInterface
     */
    public function file(string|\SplFileInfo $file): PsrResponseInterface;

    /**
     * @param string $file
     * @param string $name
     * @return PsrResponseInterface
     */
    public function download(string $file, string $name = ''): PsrResponseInterface;

    /**
     * @param string $url
     * @param int $status
     * @return PsrResponseInterface
     */
    public function redirect(string $url, int $status = 302): PsrResponseInterface;

    /**
     * @param string $data
     * @return bool
     */
    public function write(string $data): bool;

    /**
     * @param Cookie $cookie
     * @return ResponseInterface
     */
    public function withCookie(Cookie $cookie): ResponseInterface;

    /**
     * @return array
     */
    public function getCookies(): array;
}