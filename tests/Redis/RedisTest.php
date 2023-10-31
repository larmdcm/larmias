<?php

declare(strict_types=1);

namespace LarmiasTest\Redis;

class RedisTest extends TestCase
{
    /**
     * @return void
     */
    public function testConnect(): void
    {
        $redis = $this->getRedis();
        $this->assertTrue($redis->ping());
        $redis->set('larmias_connect_key', uniqid());
        $this->assertTrue($redis->exists('larmias_connect_key') > 0);
    }

    /**
     * @return void
     */
    public function testStr(): void
    {
        $redis = $this->getRedis();
        $key = 'larmias_redis_1';
        $key2 = 'larmias_redis_2';
        $value = uniqid();
        $this->assertTrue($redis->set($key, $value));
        $this->assertFalse($redis->setnx($key, $value));
        $this->assertSame($value, $redis->get($key));
        $this->assertTrue($redis->del($key) > 0);
        $redis->setex($key2, 1, $value);
        $this->assertSame($value, $redis->get($key2));
        sleep(2);
        $this->assertFalse($redis->exists($key2) > 0);
    }
}