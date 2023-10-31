<?php

declare(strict_types=1);

namespace LarmiasTest\Cache;

use Larmias\Contracts\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

class CacheTest extends TestCase
{
    /**
     * @return void
     * @throws \Throwable|InvalidArgumentException
     */
    public function testFileDriver(): void
    {
        $this->testDriver($this->getFileDriver());
    }

    /**
     * @return void
     * @throws \Throwable|InvalidArgumentException
     */
    public function testRedisDriver(): void
    {
        $this->testDriver($this->getRedisDriver());
    }

    /**
     * @param CacheInterface $cache
     * @return void
     * @throws InvalidArgumentException
     */
    protected function testDriver(CacheInterface $cache): void
    {
        $key = 'larmias_cache_1';
        $value = uniqid();
        $this->assertTrue($cache->set($key, $value));
        $this->assertSame($cache->get($key), $value);
        $this->assertTrue($cache->has($key));
        $this->assertTrue($cache->delete($key));
        $this->assertFalse($cache->has($key));

        $this->assertTrue($cache->set($key, $value, 1));
        $this->assertSame($cache->get($key), $value);
        sleep(2);
        $this->assertFalse($cache->has($key));

        $multiple = ['larmias_cache_1' => uniqid(), 'larmias_cache_2' => uniqid()];
        $this->assertTrue($cache->setMultiple($multiple, 1));
        $this->assertSame($cache->getMultiple(array_keys($multiple)), $multiple);
        $this->assertTrue($cache->deleteMultiple(array_keys($multiple)));

        $this->assertTrue($cache->clear());
    }
}