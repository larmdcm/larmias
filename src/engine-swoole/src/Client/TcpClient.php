<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole\Client;

use Larmias\Contracts\Client\ClientInterface;
use Swoole\Coroutine\Client as CoClient;

class TcpClient implements ClientInterface
{
    protected CoClient $client;

    public function __construct()
    {
        $this->client = new CoClient(SWOOLE_SOCK_TCP);
    }

    public function connect(string $host, int $port, float $timeout = 0): bool
    {
        return $this->client->connect($host, $port, $timeout);
    }

    public function isConnected(): bool
    {
        return $this->client->isConnected();
    }

    public function set(array $settings = []): ClientInterface
    {
        $this->client->set($settings);
        return $this;
    }

    public function getSocket(): mixed
    {
        return $this->client->exportSocket();
    }

    public function send(mixed $data): mixed
    {
        return $this->client->send($data);
    }

    public function recv(): mixed
    {
        return $this->client->recv();
    }

    public function close(): bool
    {
        return $this->client->close();
    }

    public function getRawConnection(): CoClient
    {
        return $this->client;
    }
}