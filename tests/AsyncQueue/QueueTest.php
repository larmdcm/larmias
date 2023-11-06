<?php

declare(strict_types=1);

namespace LarmiasTest\AsyncQueue;

class QueueTest extends TestCase
{
    public function testPush(): void
    {
        $id = session_create_id();
        $queue = $this->getQueue();
        $driver = $queue->driver();
        $this->assertNotEmpty($queue->push(new ExampleJob(), ['id' => $id]));
        var_dump($driver->status());
        $message = $driver->pop();
        $this->assertSame($id, $message->getData()['id']);
        var_dump($driver->status());
        $this->assertTrue($driver->ack($message));
        var_dump($driver->status());
    }

    public function testStatus(): void
    {
        $driver = $this->getQueue()->driver();
        var_dump($driver->status());
        $this->assertTrue(true);
    }

    public function testRestoreFail(): void
    {
        $driver = $this->getQueue()->driver();
        $this->assertTrue($driver->restoreFailMessage() > 0);
    }

    public function testClear(): void
    {
        $driver = $this->getQueue()->driver();
        var_dump($driver->status());
        $this->assertTrue($driver->clear());
        var_dump($driver->status());
    }

    public function testConsumer(): void
    {
        $this->getQueue()->driver()->consumer();
    }
}