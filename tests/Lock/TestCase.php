<?php

declare(strict_types=1);

namespace Larmias\Tests\Lock;

use Larmias\Contracts\ConfigInterface;
use Larmias\Contracts\LockerFactoryInterface;
use Larmias\Contracts\LockerInterface;
use Larmias\Contracts\Redis\RedisFactoryInterface;
use Larmias\Lock\Locker;
use Larmias\Lock\LockerFactory;
use Larmias\Redis\RedisFactory;
use Larmias\Utils\ApplicationContext;
use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    /**
     * @return void
     * @throws \Throwable
     */
    public function setUp(): void
    {
        $container = ApplicationContext::getContainer();
        /** @var ConfigInterface $config */
        $config = $container->get(ConfigInterface::class);
        $config->set([
            'redis' => [
                'default' => [
                    'host' => '127.0.0.1',
                    'port' => 6379,
                    'auth' => '',
                    'db' => 0,
                    'timeout' => 0.0,
                    'options' => [],
                    'pool' => [
                        'min_active' => 1,
                        'max_active' => 10,
                        'wait_timeout' => 3.0,
                        'heartbeat' => 0.0,
                        'max_idle_time' => 60.0,
                        'max_lifetime' => 0.0,
                    ],
                ]
            ]
        ]);
        $container->bind(RedisFactoryInterface::class, RedisFactory::class);
        $container->bind(LockerInterface::class, Locker::class);
        $container->bind(LockerFactoryInterface::class, LockerFactory::class);
    }
}