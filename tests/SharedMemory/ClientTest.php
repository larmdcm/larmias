<?php

declare(strict_types=1);

namespace LarmiasTest\SharedMemory;

use Larmias\SharedMemory\Client\Client;

class ClientTest extends TestCase
{
    /**
     * @return void
     */
    public function testStr(): void
    {
        $client = new Client(['password' => '123456', 'break_reconnect' => false]);
        $this->assertTrue($client->str->set("key1", "hello"));
        $this->assertTrue($client->str->set("key2", "world"));
        $this->assertTrue($client->str->exists("key1"));
        $this->assertSame(2, $client->str->count());
        $this->assertSame('hello', $client->str->get("key1"));
        $this->assertSame('world', $client->str->get("key2"));
        $this->assertSame('1', $client->str->incr('key3'));
        $this->assertSame('0', $client->str->decr('key3'));
        $this->assertTrue($client->str->clear());
        $this->assertSame(0, $client->str->count());
        $this->assertTrue($client->close());
    }
}