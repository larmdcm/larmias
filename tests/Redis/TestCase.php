<?php

declare(strict_types=1);

namespace LarmiasTest\Redis;

use Larmias\Context\ApplicationContext;
use Larmias\Redis\Redis;
use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    public function getRedis(): Redis
    {
        return ApplicationContext::getContainer()->get(Redis::class);
    }
}