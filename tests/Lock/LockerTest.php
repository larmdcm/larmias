<?php

declare(strict_types=1);

namespace LarmiasTest\Lock;

use Larmias\Lock\LockUtils;

class LockerTest extends TestCase
{
    /**
     * @return void
     * @throws \Throwable
     */
    public function testAcquire(): void
    {
        LockUtils::acquire(__FUNCTION__, function () {
            sleep(2);
        });
        $this->assertTrue(true);
    }
}