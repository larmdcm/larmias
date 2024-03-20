<?php

declare(strict_types=1);

namespace LarmiasTest\SharedMemory;

use Larmias\SharedMemory\Client\Connection;

class ClientTest extends TestCase
{
    /**
     * @return void
     */
    public function testStr(): void
    {
        $client = new Connection(['password' => '123456', 'break_reconnect' => false]);
        $this->assertTrue($client->strSet("key1", "hello"));
        $this->assertTrue($client->strSet("key2", "world"));
        $this->assertTrue($client->strExists("key1"));
        $this->assertSame(2, $client->strCount());
        $this->assertSame('hello', $client->strGet("key1"));
        $this->assertSame('world', $client->strGet("key2"));
        $this->assertSame('1', $client->strIncr('key3'));
        $this->assertSame('0', $client->strDecr('key3'));
        $this->assertTrue($client->strClear());
        $this->assertSame(0, $client->strCount());
        $this->assertTrue($client->close());
    }
}