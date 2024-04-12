<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole\Tcp;

use Larmias\Contracts\Tcp\ConnectionInterface;
use Larmias\Contracts\ProtocolInterface;
use Larmias\Stringable\StringBuffer;
use Larmias\Support\ProtocolHandler;
use Swoole\Coroutine;
use Swoole\Coroutine\Channel;
use Swoole\Coroutine\Server\Connection as TcpConnection;
use Closure;

class Connection implements ConnectionInterface
{
    /**
     * @var ProtocolInterface|null
     */
    protected ?ProtocolInterface $protocol = null;
    
    /**
     * @var Channel
     */
    protected Channel $channel;

    /**
     * @var array
     */
    protected array $options = [];

    /**
     * @var bool
     */
    protected bool $closed = false;

    /**
     * @param int $id
     * @param TcpConnection $connection
     */
    public function __construct(protected int $id, protected TcpConnection $connection)
    {
        $this->channel = new Channel();
    }

    /**
     * @param mixed $data
     * @return mixed
     */
    public function send(mixed $data): mixed
    {
        return $this->connection->send($this->protocol ? $this->protocol->pack($data) : $data);
    }

    /**
     * @return mixed
     */
    public function recv(): mixed
    {
        return $this->channel->pop();
    }

    /**
     * @return void
     */
    public function startCoRecv(): void
    {
        Coroutine::create(function () {
            $protocolHandler = new ProtocolHandler($this->protocol);
            while (!$this->closed) {
                $data = $this->connection->recv();
                if ($data === '' || $data === false) {
                    $this->channel->push($data);
                    break;
                }
                $protocolHandler->handle($data, function (mixed $packageData) {
                    $this->channel->push($packageData);
                });
            }
        });
    }

    /**
     * @return bool
     */
    public function close(): bool
    {
        if (!$this->closed) {
            $this->closed = $this->connection->close();
        }

        return $this->closed;
    }

    /**
     * @param array $options
     * @return self
     */
    public function setOptions(array $options): self
    {
        $this->options = array_merge($this->options, $options);
        if (!empty($options['protocol'])) {
            $this->protocol = $this->newProtocol();
        }
        return $this;
    }

    /**
     * @return TcpConnection
     */
    public function getRawConnection(): TcpConnection
    {
        return $this->connection;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getFd(): int
    {
        return $this->connection->exportSocket()->fd;
    }

    /**
     * @return ProtocolInterface|null
     */
    protected function newProtocol(): ?ProtocolInterface
    {
        $protocol = $this->options['protocol'] ?? null;
        if (!$protocol) {
            return null;
        }

        if ($protocol instanceof Closure) {
            return $protocol($this);
        }

        return new $protocol();
    }
}