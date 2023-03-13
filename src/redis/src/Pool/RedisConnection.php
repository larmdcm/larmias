<?php

declare(strict_types=1);

namespace Larmias\Redis\Pool;

use Larmias\Contracts\Redis\ConnectionInterface;
use Larmias\Pool\Connection as BaseConnection;
use Redis;

class RedisConnection extends BaseConnection implements ConnectionInterface
{
    /**
     * @var array
     */
    protected array $config = [
        'host' => '127.0.0.1',
        'port' => 6379,
        'auth' => null,
        'db' => 0,
        'timeout' => 0.0,
        'options' => [],
    ];

    /**
     * @var Redis
     */
    protected Redis $redis;

    /**
     * @var int
     */
    protected int $database = 0;

    /**
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = \array_merge($this->config, $config);
    }

    /**
     * @return bool
     */
    public function connect(): bool
    {
        return $this->reconnect();
    }

    /**
     * @return bool
     */
    public function reset(): bool
    {
        $this->setDatabase((int)$this->config['db']);
        return $this->redis->select($this->getDatabase()) !== false;
    }

    /**
     * @return bool
     */
    public function isConnected(): bool
    {
        return isset($this->redis) && $this->redis->isConnected();
    }

    /**
     * @return bool
     */
    public function reconnect(): bool
    {
        if (isset($this->redis)) {
            $this->close();
        }

        $redis = $this->createRedis((string)$this->config['host'], (int)$this->config['port'], (float)$this->config['timeout']);
        $options = $this->config['options'] ?? [];

        foreach ($options as $name => $value) {
            $redis->setOption($name, $value);
        }

        if (isset($this->config['auth']) && $this->config['auth'] !== '') {
            $redis->auth($this->config['auth']);
        }

        $db = (int)$this->config['db'];
        if ($db > 0) {
            $redis->select($db);
            $this->setDatabase($db);
        }

        $this->redis = $redis;

        return true;
    }

    /**
     * @return bool
     */
    public function close(): bool
    {
        $result = $this->redis->close();
        if ($result) {
            unset($this->redis);
        }
        return $result;
    }

    /**
     * @return Redis
     */
    public function getRaw(): Redis
    {
        return $this->redis;
    }

    /**
     * @return int
     */
    public function getDatabase(): int
    {
        return $this->database;
    }

    /**
     * @param int $db
     * @return void
     */
    public function setDatabase(int $db): void
    {
        $this->database = $db;
    }

    /**
     * @param string $host
     * @param int $port
     * @param float $timeout
     * @return Redis
     */
    protected function createRedis(string $host, int $port, float $timeout): Redis
    {
        $redis = new Redis();
        if (!$redis->connect($host, $port, $timeout)) {
            throw new \RuntimeException('Connection reconnect failed.');
        }
        return $redis;
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     * @throws \Throwable
     */
    public function __call(string $name, array $arguments)
    {
        return $this->redis->{$name}(...$arguments);
    }
}