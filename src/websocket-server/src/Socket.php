<?php

declare(strict_types=1);

namespace Larmias\WebSocketServer;

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\WebSocket\ConnectionInterface;
use Larmias\WebSocketServer\Contracts\ConnectionManagerInterface;
use Larmias\WebSocketServer\Contracts\PusherInterface;
use Larmias\WebSocketServer\Contracts\RoomInterface;

class Socket
{
    /**
     * 连接id
     * @var int
     */
    protected int $id;

    /**
     * @var bool
     */
    protected bool $closed = false;

    public function __construct(
        protected ContainerInterface         $container,
        protected ConnectionManagerInterface $connectionManager,
        protected RoomInterface              $room,
    )
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
     * 加入房间.
     * @param array|string $rooms
     * @return self
     */
    public function join(array|string $rooms): self
    {
        $this->room->join($this->id, $rooms);
        return $this;
    }

    /**
     * 离开房间.
     * @param array|string $rooms
     * @return self
     */
    public function leave(array|string $rooms): self
    {
        $this->room->leave($this->id, $rooms);
        return $this;
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
     * 是否已关闭连接
     * @return bool
     */
    public function isClosed(): bool
    {
        return $this->closed;
    }

    /**
     * 关闭连接
     * @return bool
     */
    public function close(): bool
    {
        if ($this->closed) {
            return true;
        }

        $this->closed = true;

        return (bool)$this->getConnection()?->close();
    }
}