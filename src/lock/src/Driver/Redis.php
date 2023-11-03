<?php

declare(strict_types=1);

namespace Larmias\Lock\Driver;

use Larmias\Contracts\Redis\ConnectionInterface;
use Larmias\Contracts\Redis\RedisFactoryInterface;
use function session_create_id;

class Redis extends Driver
{
    /**
     * @var ConnectionInterface
     */
    protected ConnectionInterface $connection;

    /**
     * @var string
     */
    protected string $value;

    /**
     * @param RedisFactoryInterface $factory
     */
    public function initialize(RedisFactoryInterface $factory)
    {
        $this->connection = $factory->get();
        $this->value = session_create_id();
    }

    /**
     * 获取锁
     *
     * @return bool
     */
    public function acquire(): bool
    {
        $script = $this->getLockScript();
        return $this->connection->eval($script, [
                $this->key->getKey(), $this->value, $this->connection->getDatabase(), $this->key->getTtl()
            ], 2) == 1;
    }

    /**
     * 释放锁
     *
     * @return bool
     */
    public function release(): bool
    {
        $script = $this->getUnLockScript();
        return $this->connection->eval($script, [
                $this->key->getKey(), $this->value, $this->connection->getDatabase()
            ], 2) !== false;
    }

    /**
     * 获取锁定的lua脚本
     *
     * @return string
     */
    protected function getLockScript(): string
    {
        $script = <<<LUA
local key     = KEYS[1]
local content = KEYS[2]
local db      = ARGV[1]
local ttl     = ARGV[2]
redis.call('select', db)
local lockSet = redis.call('setnx', key, content)
if lockSet == 1 then
	redis.call('pexpire', key, ttl)
else
	local value = redis.call('get', key)
	if(value == content) then
		lockSet = 1;
		redis.call('pexpire', key, ttl)
	end
end
return lockSet
LUA;
        return $script;
    }

    /**
     * 获取解锁脚本内容
     *
     * @return string
     */
    protected function getUnLockScript(): string
    {
        $script = <<<SCRIPT
local key     = KEYS[1]
local content = KEYS[2]
local db      = ARGV[1]
redis.call('select', db)
local value = redis.call('get', key)
if value == content then
  return redis.call('del', key);
end
return 0
SCRIPT;
        return $script;
    }
}