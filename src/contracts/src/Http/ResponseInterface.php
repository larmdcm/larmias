<?php

declare(strict_types=1);

namespace Larmias\Contracts\Http;

interface ResponseInterface
{
    /**
     * set Header.
     * @param string $name
     * @param mixed $value
     * @return ResponseInterface
     */
    public function header(string $name, mixed $value): ResponseInterface;

    /**
     * @param array $headers
     * @return ResponseInterface
     */
    public function withHeaders(array $headers): ResponseInterface;

    /**
     * @param string $name
     * @return ResponseInterface
     */
    public function withoutHeader(string $name): ResponseInterface;

    /**
     * @param string $name
     * @return boolean
     */
    public function hasHeader(string $name): bool;

    /**
     * @param string $name
     * @param mixed $default
     * @return string|array
     */
    public function getHeader(string $name, mixed $default = null): string|array;

    /**
     * @return array
     */
    public function getHeaders(): array;

    /**
     * 设置响应状态码
     * @param integer $statusCode
     * @param string|null $reason
     * @return ResponseInterface
     */
    public function status(int $statusCode, ?string $reason = null): ResponseInterface;

    /**
     * 获取响应状态码
     * @return integer
     */
    public function getStatusCode(): int;

    /**
     * @return string|null
     */
    public function getReason(): ?string;

    /**
     * 设置cookie
     * @param string $name
     * @param string $value
     * @param int $expire
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httpOnly
     * @param string|null $sameSite
     * @return ResponseInterface
     */
    public function cookie(string $name, string $value = '', int $expire = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httpOnly = true, ?string $sameSite = null): ResponseInterface;

    /**
     * 分块传输数据
     * @param string $data
     * @return bool
     */
    public function write(string $data): bool;

    /**
     * 发送文件.
     * @param string $file
     * @param int $offset
     * @param int $length
     */
    public function sendFile(string $file, int $offset = 0, int $length = 0): void;

    /**
     * 结束请求.
     * @param string $data
     * @return void
     */
    public function end(string $data = ''): void;
}