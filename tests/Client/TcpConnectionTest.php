<?php

declare(strict_types=1);

namespace LarmiasTest\Client;

use Larmias\Client\Connections\TcpConnection;
use Larmias\Client\Socket;
use Larmias\Codec\Packer\FramePacker;
use Larmias\Context\ApplicationContext;
use PHPUnit\Framework\TestCase;

class TcpConnectionTest extends TestCase
{
    /**
     * @return void
     * @throws \Throwable
     */
    public function testConn(): void
    {
        $conn = new TcpConnection(ApplicationContext::getContainer(), [
            'packer_class' => FramePacker::class,
        ]);
        $this->assertTrue($conn->connect());
        $this->assertTrue($conn->isConnected());
        $this->assertTrue($conn->send("hello") > 0);
        $this->assertTrue($conn->close());
        $this->assertFalse($conn->isConnected());
    }

    /**
     * @return void
     */
    public function testSocket(): void
    {
        $socket = new Socket();
        $socket->set(['rw_timeout' => 1]);
        $this->assertTrue($socket->connect('127.0.0.1', 2000, 30));
        $this->assertTrue($socket->isConnected());
        var_dump($socket->send("hello"));
        var_dump($socket->recv());
        $this->assertTrue($socket->close());
        $this->assertFalse($socket->isConnected());
    }
}