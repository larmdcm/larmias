<?php

declare(strict_types=1);

namespace Larmias\Client;

use Larmias\Contracts\Client\AsyncSocketInterface;
use Larmias\Contracts\Client\SocketInterface;
use Larmias\Contracts\EventLoopInterface;
use Larmias\Contracts\PackerInterface;
use Larmias\Stringable\StringBuffer;
use Larmias\Support\Traits\HasEvents;
use Throwable;

class AsyncSocket implements AsyncSocketInterface
{
    use HasEvents;

    /**
     * @var array
     */
    protected array $config = [
        'max_read_size' => 65535,
        'packer_class' => null,
    ];

    /**
     * @var PackerInterface|null
     */
    protected ?PackerInterface $packer = null;

    /**
     * 接收数据缓冲区
     * @var StringBuffer
     */
    protected StringBuffer $recvBuffer;

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
        $this->recvBuffer = new StringBuffer();
        $this->sendBuffer = new StringBuffer();
        $this->socket = $socket ?: new Socket();
        if ($this->socket->isConnected()) {
            $this->eventLoop->onReadable($this->socket->getSocket(), [$this, 'onRead']);
        }
    }

    /**
     * @return void
     */
    public function onRead(): void
    {
        $buffer = $this->socket->recv($this->config['max_read_size']);

        if ($buffer === '' || $buffer === false || !$this->isConnected()) {
            $this->close();
            return;
        }

        $this->recvBuffer->append($buffer);

        if ($this->packer !== null) {
            $this->handleProtocolMessage();
            return;
        }

        if ($this->recvBuffer->isEmpty()) {
            return;
        }

        $this->handleMessage($this->recvBuffer->toString());

        $this->recvBuffer->flush();
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
     * @return void
     */
    protected function handleProtocolMessage(): void
    {
        while (!$this->recvBuffer->isEmpty()) {
            $bfString = $this->recvBuffer->toString();
            try {
                $unpack = $this->packer->unpack($bfString);
            } catch (Throwable) {
                $this->recvBuffer->flush();
                $unpack = [];
            }
            if (empty($unpack)) {
                break;
            }
            $this->recvBuffer->write($unpack[1]);
            $this->handleMessage($unpack[0]);
        }
    }

    /**
     * @param mixed $data
     * @return void
     */
    protected function handleMessage(mixed $data): void
    {
        $this->fireEvent(self::ON_MESSAGE, $data);
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

        if ($this->packer) {
            $data = $this->packer->pack($data);
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
            $this->socket->set([
                'blocking' => false,
            ]);
            $connected = $this->socket->connect($this->config['host'], $this->config['port'], $this->config['timeout']);
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
     * @param array $config
     * @return AsyncSocketInterface
     */
    public function set(array $config = []): AsyncSocketInterface
    {
        $this->config = array_merge($this->config, $config);
        $this->socket->set($this->config);
        if ($this->config['packer_class']) {
            $this->packer = new $this->config['packer_class'];
        }
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