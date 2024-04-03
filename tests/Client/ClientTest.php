<?php

declare(strict_types=1);

namespace LarmiasTest\Client;

use Larmias\Client\AsyncSocket;
use Larmias\Client\Socket;
use Larmias\Client\TcpClient;
use Larmias\Codec\Protocol\FrameProtocol;
use Larmias\Context\ApplicationContext;
use Larmias\Contracts\Client\AsyncSocketInterface;
use PHPUnit\Framework\TestCase;
use Swoole\Coroutine\Client as SwooleClient;

class ClientTest extends TestCase
{
    /**
     * @return void
     */
    public function testSocketConnect(): void
    {
        $socket = new Socket();
        $socket->setOptions(['rw_timeout' => 0]);
        $this->assertTrue($socket->connect('127.0.0.1', 2000, 3));
        $this->assertTrue($socket->isConnected());
        $this->assertTrue($socket->close());
        $this->assertFalse($socket->isConnected());
    }

    /**
     * @return void
     */
    public function testSocketDelayRead(): void
    {
        $socket = new Socket();
        $this->assertTrue($socket->connect('127.0.0.1', 2000, 3));
        var_dump($socket->recv());
    }

    /**
     * @return void
     */
    public function testSwooleClient(): void
    {
        $client = new SwooleClient(SWOOLE_TCP);
        $connected = $client->connect('127.0.0.1', 2000, 3);
        $this->assertTrue($connected);
        for ($i = 0; $i < 100; $i++) {
            $client->send("hello");
        }
        var_dump($client->recv());
        $this->assertTrue($client->close());
    }

    /**
     * @return void
     */
    public function testStickTcpClient(): void
    {
        $client = new TcpClient();
        $connected = $client->connect();
        for ($i = 0; $i < 100; $i++) {
            $client->send("hello");
        }
        var_dump($client->recv());
        $this->assertTrue($connected);
    }

    /**
     * @return void
     */
    public function testFrameTcpClient(): void
    {
        $client = new TcpClient();
        $client->setOptions([
            'protocol' => FrameProtocol::class,
        ]);
        $connected = $client->connect();
        for ($i = 0; $i < 100; $i++) {
            $client->send("hello");
        }
        var_dump($client->recv());
        $this->assertTrue($connected);
    }

    /**
     * @return void
     * @throws \Throwable
     */
    public function testAsyncSocket(): void
    {
        /** @var AsyncSocketInterface $socket */
        $socket = ApplicationContext::getContainer()->make(AsyncSocket::class, [], true);
        $socket->setOptions([
            'protocol' => FrameProtocol::class,
        ]);
        $this->assertTrue($socket->connect('127.0.0.1', 2000, 30));
        $this->assertTrue($socket->isConnected());
        $this->assertTrue($socket->send("hello") > 0);
        $socket->on(AsyncSocketInterface::ON_MESSAGE, function (mixed $data) {
            var_dump($data);
        });
        sleep(1);
        $this->assertTrue($socket->close());
        $this->assertFalse($socket->isConnected());
    }
}