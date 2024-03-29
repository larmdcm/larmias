<?php

declare(strict_types=1);

namespace LarmiasTest\Client;

use Larmias\Client\AsyncSocket;
use Larmias\Client\Socket;
use Larmias\Codec\Protocol\FrameProtocol;
use Larmias\Context\ApplicationContext;
use Larmias\Contracts\Client\AsyncSocketInterface;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
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

    /**
     * @return void
     * @throws \Throwable
     */
    public function testAsyncSocket(): void
    {
        /** @var AsyncSocketInterface $socket */
        $socket = ApplicationContext::getContainer()->make(AsyncSocket::class, [], true);
        $socket->set([
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