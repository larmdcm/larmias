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
        $this->assertTrue($client->clear());
        $this->assertTrue($client->set("key1", "hello"));
        $this->assertTrue($client->set("key2", "world"));
        $this->assertTrue($client->exists("key1"));
        $this->assertSame(2, $client->count());
        $this->assertSame('hello', $client->get("key1"));
        $this->assertSame('world', $client->get("key2"));
        $this->assertSame(1, $client->incr('key3'));
        $this->assertSame(0, $client->decr('key3'));
        $this->assertTrue($client->clear());
        $this->assertSame(0, $client->count());
        $this->assertTrue($client->close());
    }

    public function testQueue(): void
    {
        $client = new Connection(['auto_connect' => true, 'password' => '123456']);
        $this->assertTrue($client->enqueue('test', 'data1'));
        $this->assertFalse($client->qeIsEmpty('test'));
        $this->assertSame(1, $client->qeCount('test'));
        $this->assertSame('data1', $client->dequeue('test'));
        $this->assertNull($client->dequeue('test'));
    }
}