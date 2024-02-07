<?php

declare(strict_types=1);

namespace Larmias\Contracts\Client;

use Larmias\Contracts\Network\ConnectionInterface;

interface ClientInterface extends ConnectionInterface
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
     * 是否已连接
     * @return bool
     */
    public function isConnected(): bool;

    /**
     * 配置
     * @param array $settings
     * @return ClientInterface
     */
    public function set(array $settings = []): ClientInterface;
}