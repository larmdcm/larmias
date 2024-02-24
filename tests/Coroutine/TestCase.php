<?php

declare(strict_types=1);

namespace LarmiasTest\Coroutine;

use Larmias\Context\ApplicationContext;
use Larmias\Contracts\Sync\LockerInterface;
use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    public function newLocker(): LockerInterface
    {
        return ApplicationContext::getContainer()->get(LockerInterface::class);
    }
}