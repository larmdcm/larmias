<?php

declare(strict_types=1);

namespace Larmias\Client\Pool;

use Larmias\Pool\Connection as BaseConnection;
use Larmias\Client\TcpClient;
use Throwable;

class TcpClientConnection extends BaseConnection
{
    /**
     * @var TcpClient
     */
    protected TcpClient $client;

    /**
     * @var array
     */
    protected array $config = [];

    /**
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->config, $config);
        $this->config['auto_connect'] = false;
        $this->config['break_reconnect'] = false;
        $this->config['ping_interval'] = 0;
        $this->config['async'] = false;
        $this->config['event'] = [];
    }

    /**
     * @return bool
     */
    public function connect(): bool
    {
        $this->client = new TcpClient($this->config);
        return $this->client->connect();
    }

    /**
     * @return bool
     */
    public function isConnected(): bool
    {
        return $this->client->isConnected();
    }

    /**
     * @return bool
     * @throws Throwable
     */
    public function ping(): bool
    {
        return $this->client->ping();
    }

    /**
     * @return bool
     */
    public function close(): bool
    {
        return $this->client->close();
    }

    /**
     * @return TcpClient
     */
    public function getRawConnection(): TcpClient
    {
        return $this->client;
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     * @throws Throwable
     */
    public function __call(string $name, array $arguments)
    {
        return $this->client->{$name}(...$arguments);
    }
}