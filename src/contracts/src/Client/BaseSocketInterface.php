<?php

declare(strict_types=1);

namespace Larmias\Contracts\Client;

interface BaseSocketInterface
{
    /**
     * 连接
     * @param string $host
     * @param int $port
     * @param float $timeout
     * @return bool
     */
    public function connect(string $host, int $port, float $timeout = 0): bool;

    /**
     * 配置
     * @param array $options
     * @return BaseSocketInterface
     */
    public function setOptions(array $options = []): BaseSocketInterface;

    /**
     * 发送数据.
     * @param mixed $data
     * @return int|false
     */
    public function send(mixed $data): int|false;

    /**
     * 关闭连接.
     * @return bool
     */
    public function close(): bool;

    /**
     * 是否已连接
     * @return bool
     */
    public function isConnected(): bool;

    /**
     * 获取socket资源句柄
     * @return mixed
     */
    public function getSocket(): mixed;

    /**
     * 获取文件句柄id
     * @return int
     */
    public function getFd(): int;
}