<?php

declare(strict_types=1);

namespace Larmias\SharedMemory\Client\Pool;

use Larmias\Codec\Protocol\FrameProtocol;
use Larmias\Pool\Connection as BaseConnection;
use Larmias\SharedMemory\Client\Connection;
use Throwable;

class ClientConnection extends BaseConnection
{
    /**
     * @var array
     */
    protected array $config = [
        'host' => '127.0.0.1',
        'port' => 2000,
        'password' => '',
        'select' => 'default',
        'timeout' => 3,
        'protocol' => FrameProtocol::class,
    ];

    /**
     * @var Connection
     */
    protected Connection $conn;

    /**
     * @var string
     */
    protected string $database = 'default';

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
        $this->conn = new Connection($this->config);
        $this->setDatabase($this->config['select']);
        return $this->conn->connect();
    }

    /**
     * @return bool
     */
    public function isConnected(): bool
    {
        return $this->conn->isConnected();
    }

    /**
     * @return bool
     * @throws Throwable
     */
    public function reset(): bool
    {
        $db = (string)$this->config['select'];
        if ($db !== $this->database) {
            $this->setDatabase($db);
            return $this->conn->select($db) !== false;
        }
        return true;
    }

    /**
     * @return bool
     */
    public function ping(): bool
    {
        return $this->conn->ping();
    }

    /**
     * @return bool
     */
    public function close(): bool
    {
        return $this->conn->close();
    }

    /**
     * @return Connection
     */
    public function getRawConnection(): Connection
    {
        return $this->conn;
    }

    /**
     * @return string
     */
    public function getDatabase(): string
    {
        return $this->database;
    }

    /**
     * @param string $db
     * @return void
     */
    public function setDatabase(string $db): void
    {
        $this->database = $db;
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     * @throws Throwable
     */
    public function __call(string $name, array $arguments)
    {
        return $this->conn->{$name}(...$arguments);
    }
}