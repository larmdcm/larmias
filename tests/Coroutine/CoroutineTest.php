<?php

declare(strict_types=1);

namespace LarmiasTest\Coroutine;

use Larmias\Coroutine\Coroutine;

class CoroutineTest extends TestCase
{
    public function testStartExecutesCoroutine()
    {
        $value = null;
        $co = Coroutine::create(function () use (&$value) {
            $value = 'started';
        });
        $this->assertTrue($co->getId() > 0);
        $this->assertEquals('started', $value);
    }
}