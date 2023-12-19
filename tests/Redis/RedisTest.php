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

    /**
     * @return void
     */
    public function testTrans(): void
    {
        $redis = $this->getRedis();
        $redis->multi();
        $redis->set('larmias_redis_1', uniqid());
        $redis->set('larmias_redis_2', uniqid());
        $redis->exec();
        var_dump($redis->get('larmias_redis_1'));
        var_dump($redis->get('larmias_redis_2'));
        $this->assertTrue(true);
    }

    /**
     * @return void
     */
    public function testSet(): void
    {
        $key = 'larmias_set';
        $redis = $this->getRedis();
        $redis->del($key);
        $redis->sAdd($key, 1);
        $redis->sAdd($key, 2);
        $redis->sAdd($key, 3);
        $this->assertSame($redis->sCard($key), 3);
        $list = $redis->sMembers($key);
        $this->assertSame($list, ['1', '2', '3']);
        $this->assertTrue($redis->sRem($key, 1) > 0);
        $this->assertTrue($redis->sRem($key, 2) > 0);
        $this->assertTrue($redis->sRem($key, 3) > 0);
        $this->assertSame($redis->sCard($key), 0);
        $this->assertTrue(true);
    }
}