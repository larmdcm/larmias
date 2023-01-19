<?php

declare(strict_types=1);

namespace Larmias\HttpServer\Contracts;

use Larmias\Http\Message\Cookie;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

interface ResponseInterface
{
    /**
     * @param array|object $data
     * @param int $code
     * @param array $headers
     * @return PsrResponseInterface
     */
    public function json(array|object $data, int $code = 200, array $headers = []): PsrResponseInterface;

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
     * @param Cookie $cookie
     * @return ResponseInterface
     */
    public function withCookie(Cookie $cookie): ResponseInterface;

    /**
     * @return array
     */
    public function getCookies(): array;
}