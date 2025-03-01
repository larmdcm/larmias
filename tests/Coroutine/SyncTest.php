<?php

declare(strict_types=1);

namespace LarmiasTest\Coroutine;

use Larmias\Coroutine\Coroutine;
use Larmias\Engine\Timer;
use function Larmias\Support\println;

class SyncTest extends TestCase
{
    public function testLock(): void
    {
        $locker = $this->newLocker();
        Coroutine::create(function () use ($locker) {
            $locker->lock();
            Timer::sleep(2);
            println('1');
            $locker->unlock();
        });

        Coroutine::create(function () use ($locker) {
            $locker->lock();
            sleep(1);
            println('2');
            $locker->unlock();
        });

        Coroutine::create(function () use ($locker) {
            $locker->lock();
            Timer::sleep(2);
            println('3');
            $locker->unlock();
        });

        $this->assertTrue(true);
    }
}