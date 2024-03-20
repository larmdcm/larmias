<?php

declare(strict_types=1);

namespace Larmias\SharedMemory\Client\Pool;

use Larmias\Pool\Connection as BaseConnection;
use Larmias\SharedMemory\Client\Connection;
use Larmias\SharedMemory\Message\Command;
use Throwable;

class ClientConnection extends BaseConnection
{
    /**
     * @var array
     */
    protected array $config = [];

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
    }

    /**
     * @return bool
     */
    public function connect(): bool
    {
        $this->config['auto_connect'] = false;
        $this->config['break_reconnect'] = false;
        $this->config['ping_interval'] = 0;
        $this->config['async'] = false;
        $this->conn = new Connection($this->config);
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
        $this->setDatabase($db);
        return $this->conn->select($db) !== false;
    }

    /**
     * @return bool
     */
    public function ping(): bool
    {
        try {
            return $this->conn->sendCommand(Command::COMMAND_PING);
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function close(): bool
    {
        return $this->conn->close(true);
    }

    /**
     * @return Connection
     */
    public function getRaw(): Connection
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