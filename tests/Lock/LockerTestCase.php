<?php

declare(strict_types=1);

namespace Larmias\Tests\Lock;

use Larmias\Lock\LockUtils;

class LockerTestCase extends TestCase
{
    /**
     * @return void
     * @throws \Throwable
     */
    public function testAcquire(): void
    {
        LockUtils::acquire(__FUNCTION__, function () {
            sleep(10);
            println('执行完成.');
        });
    }
}