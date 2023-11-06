<?php

declare(strict_types=1);

namespace LarmiasTest\AsyncQueue;

class QueueTest extends TestCase
{
    public function testPush(): void
    {
        $id = session_create_id();
        $queue = $this->getQueue();
        $this->assertNotEmpty($queue->push(new ExampleJob(), ['id' => $id])->getMessageId());
    }

    public function testPop(): void
    {
        $driver = $this->getQueue()->driver();
        $this->assertNotNull($driver->pop());
    }

    public function testPopTimout(): void
    {
        $driver = $this->getQueue()->driver();
        $this->assertNotNull($driver->pop(3));
    }

    public function testAck(): void
    {
        $driver = $this->getQueue()->driver();
        $this->assertTrue($driver->ack($driver->pop()));
    }

    public function testFail(): void
    {
        $driver = $this->getQueue()->driver();
        $this->assertTrue($driver->fail($driver->pop()));
    }

    public function testMixed(): void
    {
        $id = session_create_id();
        $queue = $this->getQueue();
        $driver = $queue->driver();
        $this->assertNotEmpty($queue->push(new ExampleJob(), ['id' => $id]));
        var_dump($driver->info());
        $message = $driver->pop();
        $this->assertSame($id, $message->getData()['id']);
        var_dump($driver->info());
        $this->assertTrue($driver->ack($message));
        var_dump($driver->info());
    }

    public function testInfo(): void
    {
        $driver = $this->getQueue()->driver();
        var_dump($driver->info());
        $this->assertTrue(true);
    }

    public function testRestoreFail(): void
    {
        $driver = $this->getQueue()->driver();
        $this->assertTrue($driver->restoreFailMessage() > 0);
    }

    public function testFlush(): void
    {
        $driver = $this->getQueue()->driver();
        var_dump($driver->info());
        $this->assertTrue($driver->flush());
        var_dump($driver->info());
    }

    public function testConsumer(): void
    {
        $this->getQueue()->driver()->consumer();
    }
}