<?php

declare(strict_types=1);

namespace Larmias\Client\Connections;

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\EventLoopInterface;
use Larmias\Contracts\PackerInterface;
use Larmias\Contracts\Tcp\ConnectionInterface;
use Larmias\Stringable\StringBuffer;
use Larmias\Client\Socket;
use Throwable;
use SplQueue;

class TcpConnection implements ConnectionInterface
{
    /**
     * @var array
     */
    protected array $config = [
        'host' => '127.0.0.1',
        'port' => 2000,
        'timeout' => 3,
        'packer_class' => null,
        'max_read_size' => 87380,
        'max_package_size' => 1048576,
    ];

    /**
     * @var Socket
     */
    protected Socket $socket;

    /**
     * @var PackerInterface|null
     */
    protected ?PackerInterface $packer = null;

    /**
     * @var EventLoopInterface
     */
    protected EventLoopInterface $eventLoop;

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
     * @var SplQueue
     */
    protected SplQueue $messageQueue;

    /**
     * @param ContainerInterface $container
     * @param array $config
     * @throws Throwable
     */
    public function __construct(
        protected ContainerInterface $container,
        array                        $config = []
    )
    {
        $this->config = array_merge($this->config, $config);
        $this->eventLoop = $this->container->get(EventLoopInterface::class);
        if ($this->config['packer_class']) {
            $this->packer = $this->container->get($this->config['packer_class']);
        }
        $this->recvBuffer = new StringBuffer();
        $this->sendBuffer = new StringBuffer();
        $this->messageQueue = new SplQueue();
        $this->socket = new Socket();
        $this->socket->set([
            'blocking' => true,
            'read_buffer_size' => 0,
            'write_buffer_size' => 0,
        ]);
    }

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
    public function handleProtocolMessage(): void
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
        $this->messageQueue->enqueue($data);
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
     * @return mixed
     */
    public function recv(): mixed
    {
        if ($this->messageQueue->isEmpty()) {
            return null;
        }

        return $this->messageQueue->dequeue();
    }

    /**
     * @return bool
     */
    public function connect(): bool
    {
        $connected = $this->socket->connect($this->config['host'], $this->config['port'], $this->config['timeout']);
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
     * @return Socket
     */
    public function getRawConnection(): Socket
    {
        return $this->socket;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->socket->getFd();
    }

    /**
     * @return int
     */
    public function getFd(): int
    {
        return $this->socket->getFd();
    }
}