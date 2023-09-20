<?php

declare(strict_types=1);

namespace Larmias\WebSocketServer;

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\WebSocket\ConnectionInterface;
use Larmias\WebSocketServer\Contracts\ConnectionManagerInterface;
use Larmias\WebSocketServer\Contracts\PusherInterface;

class Socket
{
    /**
     * 连接id
     * @var int
     */
    protected int $id;

    public function __construct(protected ContainerInterface $container, protected ConnectionManagerInterface $connectionManager)
    {
    }

    /**
     * 给指定连接推送数据
     * @param ...$values
     * @return PusherInterface
     */
    public function to(...$values): PusherInterface
    {
        return $this->makePusher()->to(...$values);
    }

    /**
     * 给当前连接推送数据
     * @param mixed $data
     * @return void
     */
    public function push(mixed $data): void
    {
        $this->to($this->getId())->push($data);
    }

    /**
     * 给当前连接事件消息
     * @param string $event
     * @param ...$data
     * @return void
     */
    public function emit(string $event, ...$data): void
    {
        $this->makePusher()->to($this->id)->emit($event, ...$data);
    }

    /**
     * @return PusherInterface
     */
    public function makePusher(): PusherInterface
    {
        /** @var PusherInterface $pusher */
        $pusher = $this->container->make(PusherInterface::class, [], true);
        return $pusher;
    }

    /**
     * 获取连接id
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }


    /**
     * 设置连接id
     * @param int $id
     * @return self
     */
    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * 获取连接
     * @return ConnectionInterface|null
     */
    public function getConnection(): ?ConnectionInterface
    {
        return $this->connectionManager->get($this->id);
    }

    /**
     * 关闭连接
     * @return bool
     */
    public function close(): bool
    {
        return (bool)$this->getConnection()?->close();
    }
}