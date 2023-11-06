<?php

declare(strict_types=1);

namespace LarmiasTest\Session;

use SessionHandlerInterface;

class SessionTest extends TestCase
{
    /**
     * @return void
     */
    public function testSession(): void
    {
        $session = $this->getSession();
        $sessionId = '813ae23d1183fe548d4e3474c22883a2';
        $session->setId($sessionId);
        $session->start();
        $key = 'larmias_session_1';
        $value = uniqid();
        var_dump($session->all());
        $this->assertTrue($session->set($key, $value));
        $this->assertSame($session->get($key), $value);
        $this->assertTrue($session->has($key));
        $this->assertTrue($session->delete($key));
        $this->assertFalse($session->has($key));
        $this->assertTrue($session->set($key, $value));
        $this->assertTrue($session->save());
    }

    /**
     * @return void
     */
    public function testFileSessionHandler(): void
    {
        $this->testSessionHandler($this->getFileHandler());
    }

    /**
     * @return void
     */
    public function testRedisSessionHandler(): void
    {
        $this->testSessionHandler($this->getRedisHandler());
    }

    protected function testSessionHandler(SessionHandlerInterface $handler)
    {
        $sessionId = '813ae23d1183fe548d4e3474c22883a2';
        $value = uniqid();
        $this->assertTrue($handler->write($sessionId, $value));
        $this->assertSame($value, $handler->read($sessionId));
        $this->assertTrue($handler->destroy($sessionId));
        $this->assertEmpty($handler->read($sessionId));
        $this->assertTrue($handler->close());
    }
}