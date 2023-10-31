<?php

declare(strict_types=1);

namespace LarmiasTest\Cache;

use Larmias\Cache\Driver\File;
use Larmias\Cache\Driver\Redis;
use Larmias\Context\ApplicationContext;
use Larmias\Contracts\CacheInterface;
use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    /**
     * @return void
     * @throws \Throwable
     */
    public function setUp(): void
    {
    }

    public function getFileDriver(): File
    {
        return $this->getCache()->store('file');
    }

    public function getRedisDriver(): Redis
    {
        return $this->getCache()->store('redis');
    }

    public function getCache(): CacheInterface
    {
        return ApplicationContext::getContainer()->get(CacheInterface::class);
    }
}