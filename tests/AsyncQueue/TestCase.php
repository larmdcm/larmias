<?php

declare(strict_types=1);

namespace LarmiasTest\AsyncQueue;

use Larmias\AsyncQueue\Contracts\QueueInterface;
use Larmias\Context\ApplicationContext;
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

    public function getQueue(): QueueInterface
    {
        return ApplicationContext::getContainer()->get(QueueInterface::class);
    }
}