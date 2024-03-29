<?php

declare(strict_types=1);

namespace LarmiasTest\Support;

use Larmias\Codec\Protocol\FrameProtocol;
use Larmias\Support\ProtocolHandler;

class ProtocolTestCase extends TestCase
{
    public function testEmptyProtocolHandler(): void
    {
        $handler = new ProtocolHandler();
        $handler->handle('hello', function ($data) {
            $this->assertSame($data, 'hello');
        });
        $handler->handle('world', function ($data) {
            $this->assertSame($data, 'world');
        });
        $handler->handle('', function ($data) {
            $this->assertSame($data, '');
        });
    }

    public function testFrameProtocolHandler(): void
    {
        $protocol = new FrameProtocol();
        $handler = new ProtocolHandler($protocol);
        $handler->handle($protocol->pack('hello'), function ($data) {
            $this->assertSame($data, 'hello');
        });
        $handler->handle($protocol->pack('world'), function ($data) {
            $this->assertSame($data, 'world');
        });
        $handler->handle($protocol->pack(''), function ($data) {
            $this->assertSame($data, '');
        });
    }
}