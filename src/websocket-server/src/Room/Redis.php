<?php

declare(strict_types=1);

namespace Larmias\WebSocketServer\Room;

use InvalidArgumentException;
use Larmias\Contracts\Redis\ConnectionInterface;
use Larmias\Contracts\Redis\RedisFactoryInterface;
use Larmias\WebSocketServer\Contracts\RoomInterface;

class Redis implements RoomInterface
{
    /**
     * @var ConnectionInterface
     */
    protected ConnectionInterface $redis;

    /**
     * @var string[]
     */
    protected array $config = [
        'prefix' => 'larmias_ws',
        'rooms_key' => 'rooms',
        'descriptors_key' => 'fds',
        'redis_name' => 'default',
    ];

    /**
     * @param RedisFactoryInterface $redisFactory
     */
    public function __construct(protected RedisFactoryInterface $redisFactory)
    {
        $this->redis = $this->redisFactory->get($this->config['redis_name']);
    }

    /**
     * 加入房间
     * @param string $id
     * @param array|string $rooms
     * @return void
     */
    public function join(string $id, array|string $rooms): void
    {
        $rooms = (array)$rooms;
        $this->addValue($this->config['descriptors_key'], $id, $rooms);
        foreach ($rooms as $room) {
            $this->addValue($this->config['rooms_key'], $room, $id);
        }
    }

    /**
     * 离开房间
     * @param string $id
     * @param array|string $rooms
     * @return void
     */
    public function leave(string $id, array|string $rooms): void
    {
        $rooms = (array)$rooms;
        if (empty($rooms)) {
            $rooms = $this->getValue($this->config['descriptors_key'], $id);
        }
        $this->removeValue($this->config['descriptors_key'], $id, $rooms);

        foreach ($rooms as $room) {
            $this->removeValue($this->config['rooms_key'], $room, $id);
        }
    }

    /**
     * 获取房间的所有客户端
     * @param string $room
     * @return array
     */
    public function getClients(string $room): array
    {
        return $this->getValue($this->config['rooms_key'], $room);
    }

    /**
     * @param string $table
     * @param string $key
     * @param array|string $values
     * @return void
     */
    protected function addValue(string $table, string $key, array|string $values): void
    {
        $this->checkTable($table);
        $redisKey = $this->getKey($table, $key);
        $values = (array)$values;
        $this->redis->multi();
        foreach ($values as $value) {
            $this->redis->sAdd($redisKey, $value);
        }
        $this->redis->exec();
    }

    /**
     * @param string $table
     * @param string $key
     * @param array|string $values
     * @return void
     */
    protected function removeValue(string $table, string $key, array|string $values): void
    {
        $this->checkTable($table);
        $redisKey = $this->getKey($table, $key);
        $values = (array)$values;
        $this->redis->multi();
        foreach ($values as $value) {
            $this->redis->sRem($redisKey, $value);
        }
        $this->redis->exec();
    }

    /**
     * @param string $key
     * @param string $table
     * @return array
     */
    protected function getValue(string $table, string $key): array
    {
        $this->checkTable($table);
        return $this->redis->sMembers($this->getKey($table, $key));
    }

    /**
     * 检查table key
     * @param string $table
     * @return void
     */
    protected function checkTable(string $table): void
    {
        if (!in_array($table, [$this->config['rooms_key'], $this->config['descriptors_key']])) {
            throw new InvalidArgumentException("Invalid table name: `{$table}`.");
        }
    }

    /**
     * 获取redis key
     * @param string $table
     * @param string $key
     * @return string
     */
    protected function getKey(string $table, string $key): string
    {
        return "{$this->config['prefix']}:{$table}:{$key}";
    }
}