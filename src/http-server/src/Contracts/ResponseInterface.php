<?php

declare(strict_types=1);

namespace Larmias\HttpServer\Contracts;

use Larmias\Http\Message\Cookie;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Closure;
use Stringable;
use SplFileInfo;

interface ResponseInterface
{
    /**
     * @param array|object|string $data
     * @param string $charset
     * @return PsrResponseInterface
     */
    public function json(array|object|string $data, string $charset = 'utf-8'): PsrResponseInterface;

    /**
     * @param array|object|string $data
     * @param string $root
     * @param string $charset
     * @return PsrResponseInterface
     */
    public function xml(array|object|string $data, string $root = 'root', string $charset = 'utf-8'): PsrResponseInterface;

    /**
     * @param string|Stringable $data
     * @param string $charset
     * @return PsrResponseInterface
     */
    public function raw(string|Stringable $data, string $charset = 'utf-8'): PsrResponseInterface;

    /**
     * @param string|Stringable $data
     * @param string $charset
     * @return PsrResponseInterface
     */
    public function html(string|Stringable $data, string $charset = 'utf-8'): PsrResponseInterface;

    /**
     * @param string|SplFileInfo $file
     * @return PsrResponseInterface
     */
    public function file(string|SplFileInfo $file): PsrResponseInterface;

    /**
     * @param string|SplFileInfo $file
     * @param string $name
     * @return PsrResponseInterface
     */
    public function download(string|SplFileInfo $file, string $name = ''): PsrResponseInterface;

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
     * @param Closure $handler
     * @return PsrResponseInterface
     */
    public function sse(Closure $handler): PsrResponseInterface;

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