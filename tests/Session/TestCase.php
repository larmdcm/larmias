<?php

declare(strict_types=1);

namespace LarmiasTest\Session;

use Larmias\Session\Handler\FileHandler;
use Larmias\Session\Handler\RedisHandler;
use Larmias\Context\ApplicationContext;
use Larmias\Contracts\SessionInterface;
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

    public function getFileHandler(): FileHandler
    {
        return $this->getSession()->getHandler('file');
    }

    public function getRedisHandler(): RedisHandler
    {
        return $this->getSession()->getHandler('redis');
    }

    public function getSession(): SessionInterface
    {
        return ApplicationContext::getContainer()->get(SessionInterface::class);
    }
}