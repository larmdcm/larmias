<?php

declare(strict_types=1);

namespace Larmias\Client;

use Larmias\Client\Traits\Protocol;
use Larmias\Contracts\Client\AsyncSocketInterface;
use Larmias\Contracts\Client\SocketInterface;
use Larmias\Contracts\EventLoopInterface;
use Larmias\Support\Traits\HasEvents;
use Larmias\Stringable\StringBuffer;

class AsyncSocket implements AsyncSocketInterface
{
    use HasEvents, Protocol;

    /**
     * @var array
     */
    protected array $options = [
        'max_read_size' => 65535,
        'protocol' => null,
        'max_package_size' => 0,
    ];

    /**
     * 发送数据缓冲区
     * @var StringBuffer
     */
    protected StringBuffer $sendBuffer;

    /**
     * @var SocketInterface
     */
    protected SocketInterface $socket;

    /**
     * @param EventLoopInterface $eventLoop
     * @param SocketInterface|null $socket
     */
    public function __construct(
        protected EventLoopInterface $eventLoop,
        ?SocketInterface             $socket = null,
    )
    {
        $this->sendBuffer = new StringBuffer();
        $this->socket = $socket ?: new Socket();
        $this->initProtocolHandler();
        if ($this->socket->isConnected()) {
            $this->eventLoop->onReadable($this->socket->getSocket(), [$this, 'onRead']);
        }
    }

    /**
     * @return void
     */
    public function onRead(): void
    {
        $buffer = $this->socket->recv($this->options['max_read_size']);

        if ($buffer === '' || $buffer === false || !$this->isConnected()) {
            $this->close();
            return;
        }

        $this->protocolHandler->handle($buffer, function (mixed $data) {
            $this->fireEvent(self::ON_MESSAGE, $data);
        });
    }

    /**
     * @return void
     */
    public function onWrite(): void
    {
        if (!$this->isConnected()) {
            $this->close();
            return;
        }

        $buffer = $this->sendBuffer->toString();
        $dataLen = strlen($buffer);
        $len = $this->socket->send($buffer);
        if ($len === $dataLen) {
            $this->eventLoop->offWritable($this->socket->getSocket());
            $this->sendBuffer->flush();
            return;
        }
        if ($len > 0) {
            $this->sendBuffer->take($len);
        } else {
            $this->close();
        }
    }

    /**
     * @param mixed $data
     * @return int|false
     */
    public function send(mixed $data): int|false
    {
        if (!$this->isConnected()) {
            $this->close();
            return false;
        }

        if ($this->protocol) {
            $data = $this->protocol->pack($data);
            if ($data === '') {
                return false;
            }
        }

        if ($this->sendBuffer->isEmpty()) {
            $dataLen = strlen($data);
            $len = $this->socket->send($data);
            if ($len === $dataLen) {
                return $len;
            }
            if ($len > 0) {
                $this->sendBuffer->write(substr($data, $len));
            } else {
                if (!$this->isConnected()) {
                    $this->close();
                    return false;
                }
                $this->sendBuffer->write($data);
            }
            $this->eventLoop->onWritable($this->socket->getSocket(), [$this, 'onWrite']);

            if ($len > 0) {
                return $len;
            }

            return false;
        }

        $this->sendBuffer->append($data);

        return false;
    }

    /**
     * @param string $host
     * @param int $port
     * @param float $timeout
     * @return bool
     */
    public function connect(string $host, int $port, float $timeout = 0): bool
    {
        $connected = $this->socket->isConnected();

        if (!$connected) {
            $this->socket->setOptions([
                'blocking' => false,
            ]);
            $connected = $this->socket->connect($this->options['host'], $this->options['port'], $this->options['timeout']);
        }

        if ($connected) {
            $this->eventLoop->onReadable($this->socket->getSocket(), [$this, 'onRead']);
        }

        return $connected;
    }

    /**
     * @return bool
     */
    public function isConnected(): bool
    {
        return $this->socket->isConnected();
    }

    /**
     * @return bool
     */
    public function close(): bool
    {
        $this->eventLoop->offReadable($this->socket->getSocket());
        $this->eventLoop->offWritable($this->socket->getSocket());
        return $this->socket->close();
    }


    /**
     * @param array $options
     * @return AsyncSocketInterface
     */
    public function setOptions(array $options = []): AsyncSocketInterface
    {
        $this->options = array_merge($this->options, $options);
        $this->socket->setOptions($this->options);
        $this->initProtocolHandler($this->options);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSocket(): mixed
    {
        return $this->socket->getSocket();
    }

    /**
     * @return int
     */
    public function getFd(): int
    {
        return $this->socket->getFd();
    }
}