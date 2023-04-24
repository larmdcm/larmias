<?php

declare(strict_types=1);

namespace Larmias\Tests\Lock;

use Larmias\Contracts\ConfigInterface;
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
        /** @var ConfigInterface $config */
        $config = ApplicationContext::getContainer()->get(ConfigInterface::class);
        $config->set([
            'lock' => [

            ],
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
    }
}